<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Employee;
use App\Models\EmployeeLeaveAllotment;
use App\Models\LeaveAllotment;
use App\Models\LeaveType;
use App\Services\SiteAuthService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LeaveAllotmentController extends Controller
{
    private const CATEGORY_FIELDS = [
        'FRONT-DESK' => 'front_desk_leave_count',
        'GROUP-D' => 'group_d_leave_count',
        'VHS TEACHER' => 'vhs_teacher_leave_count',
    ];

    protected $siteAuthService;
    protected $data;

    public function __construct()
    {
        $this->data = [
            'title' => 'Leave Allotment',
            'controller' => 'LeaveAllotmentController',
            'controller_route' => 'payroll-leave/leave-allotment',
            'primary_key' => 'id',
        ];

        $this->siteAuthService = new SiteAuthService();
    }

    public function list(Request $request)
    {
        $data['module'] = $this->data;
        $title = $this->data['title'].' List';
        $pageName = 'payroll-leave.leave-allotment.list';
        $data['rows'] = LeaveAllotment::with('leaveType')
            ->withCount([
                'employeeAllotments as assigned_employee_count' => function ($query) {
                    $query->where('status', '!=', 3);
                },
            ])
            ->where('status', '!=', 3)
            ->orderBy('id', 'DESC')
            ->get();
        $data['action'] = 'List';

        $data = $this->siteAuthService->admin_after_login_layout($title, $pageName, $data);

        return view('front.pages.'.$pageName, $data);
    }

    public function add(Request $request)
    {
        $data['module'] = $this->data;
        $title = $this->data['title'].' Add';
        $pageName = 'payroll-leave.leave-allotment.add-edit';
        $data['row'] = null;
        $data['action'] = 'Add';
        $data['leaveTypes'] = $this->leaveTypeOptions();

        if ($request->isMethod('post')) {
            $request->validate($this->validationRules());

            LeaveAllotment::create($this->payload($request) + [
                'status' => 1,
                'created_by' => $this->currentUserId(),
                'updated_by' => $this->currentUserId(),
            ]);

            return redirect($this->data['controller_route'].'/list')->with('success_message', $this->data['title'].' added successfully !!!');
        }

        $data = $this->siteAuthService->admin_after_login_layout($title, $pageName, $data);

        return view('front.pages.'.$pageName, $data);
    }

    public function edit(Request $request, $id)
    {
        $data['module'] = $this->data;
        $id = Helper::decoded($id);
        $title = $this->data['title'].' Edit';
        $pageName = 'payroll-leave.leave-allotment.add-edit';
        $data['row'] = LeaveAllotment::where($this->data['primary_key'], '=', $id)
            ->where('status', '!=', 3)
            ->first();
        $data['action'] = 'Edit';

        if (! $data['row']) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'Leave allotment not found !!!');
        }

        $data['leaveTypes'] = $this->leaveTypeOptions((int) $data['row']->leave_type_id);

        if ($request->isMethod('post')) {
            $request->validate($this->validationRules($data['row']->id));

            $data['row']->update($this->payload($request) + [
                'updated_by' => $this->currentUserId(),
            ]);

            return redirect($this->data['controller_route'].'/list')->with('success_message', $this->data['title'].' updated successfully !!!');
        }

        $data = $this->siteAuthService->admin_after_login_layout($title, $pageName, $data);

        return view('front.pages.'.$pageName, $data);
    }

    public function change_status(Request $request, $id)
    {
        $id = Helper::decoded($id);
        $model = LeaveAllotment::where($this->data['primary_key'], '=', $id)
            ->where('status', '!=', 3)
            ->first();

        if (! $model) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'Leave allotment not found !!!');
        }

        if ((int) $model->status === 1) {
            $model->status = 0;
            $msg = 'deactivated';
        } else {
            $model->status = 1;
            $msg = 'activated';
        }

        $model->updated_by = $this->currentUserId();
        $model->save();

        return redirect($this->data['controller_route'].'/list')->with('success_message', $this->data['title'].' '.$msg.' successfully !!!');
    }

    public function assign(Request $request, $id)
    {
        $id = Helper::decoded($id);
        $allotment = LeaveAllotment::with('leaveType')
            ->where($this->data['primary_key'], '=', $id)
            ->where('status', '!=', 3)
            ->first();

        if (! $allotment) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'Leave allotment not found !!!');
        }

        $result = $this->assignToEmployees($allotment);

        return redirect($this->data['controller_route'].'/list')->with(
            'success_message',
            'Leave assigned to '.$result['assigned'].' employee(s). Skipped '.$result['skipped'].' employee(s).'
        );
    }

    private function assignToEmployees(LeaveAllotment $allotment): array
    {
        $assignedEmployeeIds = [];
        $assigned = 0;
        $skipped = 0;
        $userId = $this->currentUserId();

        DB::transaction(function () use ($allotment, $userId, &$assignedEmployeeIds, &$assigned, &$skipped) {
            $employees = Employee::where('status', '=', 1)
                ->orderBy('id')
                ->get();

            foreach ($employees as $employee) {
                $categories = $this->employeeCategoryValues($employee->category);
                $currentAllotment = $this->currentAllotmentForCategories($allotment, $categories);
                $previousBalance = (bool) $allotment->is_carry_forward
                    ? $this->previousBalance($allotment, (int) $employee->id)
                    : 0.0;
                $totalAllotment = $currentAllotment + $previousBalance;

                if ($totalAllotment <= 0) {
                    $skipped++;
                    continue;
                }

                $assignedEmployeeIds[] = (int) $employee->id;
                $usedLeave = $this->existingUsedLeave($allotment, (int) $employee->id);
                $balanceLeave = max(0, $totalAllotment - $usedLeave);

                EmployeeLeaveAllotment::updateOrCreate(
                    [
                        'leave_allotment_id' => (int) $allotment->id,
                        'employee_id' => (int) $employee->id,
                    ],
                    [
                        'employee_no' => (string) $employee->employee_no,
                        'employee_name' => $this->employeeName($employee),
                        'employee_category' => implode(', ', $categories),
                        'leave_type_id' => (int) $allotment->leave_type_id,
                        'leave_tenure_from' => $allotment->leave_tenure_from,
                        'leave_tenure_to' => $allotment->leave_tenure_to,
                        'current_allotment' => $currentAllotment,
                        'previous_balance' => $previousBalance,
                        'total_allotment' => $totalAllotment,
                        'used_leave' => $usedLeave,
                        'balance_leave' => $balanceLeave,
                        'status' => 1,
                        'assigned_by' => $userId,
                    ]
                );

                $assigned++;
            }

            $staleQuery = EmployeeLeaveAllotment::where('leave_allotment_id', '=', (int) $allotment->id)
                ->where('status', '!=', 3);

            if (! empty($assignedEmployeeIds)) {
                $staleQuery->whereNotIn('employee_id', $assignedEmployeeIds);
            }

            $staleQuery->update(['status' => 3]);
        });

        return [
            'assigned' => $assigned,
            'skipped' => $skipped,
        ];
    }

    private function currentAllotmentForCategories(LeaveAllotment $allotment, array $categories): float
    {
        $counts = [];

        foreach ($categories as $category) {
            $field = self::CATEGORY_FIELDS[$category] ?? null;
            if ($field === null) {
                continue;
            }

            $counts[] = (float) $allotment->{$field};
        }

        return empty($counts) ? 0.0 : max($counts);
    }

    private function previousBalance(LeaveAllotment $allotment, int $employeeId): float
    {
        $previous = EmployeeLeaveAllotment::where('employee_id', '=', $employeeId)
            ->where('leave_type_id', '=', (int) $allotment->leave_type_id)
            ->where('status', '!=', 3)
            ->whereDate('leave_tenure_to', '<', Carbon::parse($allotment->leave_tenure_from)->toDateString())
            ->orderBy('leave_tenure_to', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();

        return $previous ? (float) $previous->balance_leave : 0.0;
    }

    private function existingUsedLeave(LeaveAllotment $allotment, int $employeeId): float
    {
        $existing = EmployeeLeaveAllotment::where('leave_allotment_id', '=', (int) $allotment->id)
            ->where('employee_id', '=', $employeeId)
            ->first();

        return $existing ? (float) $existing->used_leave : 0.0;
    }

    private function validationRules($ignoreId = null): array
    {
        $tenureRule = Rule::unique('leave_allotments', 'leave_type_id')
            ->where(function ($query) {
                return $query
                    ->where('leave_tenure_from', request('leave_tenure_from'))
                    ->where('leave_tenure_to', request('leave_tenure_to'))
                    ->whereNull('deleted_at');
            });

        if ($ignoreId !== null) {
            $tenureRule->ignore($ignoreId);
        }

        return [
            'leave_tenure_from' => ['required', 'date'],
            'leave_tenure_to' => ['required', 'date', 'after_or_equal:leave_tenure_from'],
            'leave_type_id' => [
                'required',
                'integer',
                Rule::exists('leave_types', 'id')->where(function ($query) {
                    $query->where('status', '!=', 3);
                }),
                $tenureRule,
            ],
            'front_desk_leave_count' => ['nullable', 'numeric', 'min:0', 'max:365'],
            'group_d_leave_count' => ['nullable', 'numeric', 'min:0', 'max:365'],
            'tsa_teacher_leave_count' => ['nullable', 'numeric', 'min:0', 'max:0'],
            'vhs_teacher_leave_count' => ['nullable', 'numeric', 'min:0', 'max:365'],
            'is_carry_forward' => ['nullable', Rule::in(['0', '1', 0, 1])],
        ];
    }

    private function payload(Request $request): array
    {
        return [
            'leave_tenure_from' => $request->leave_tenure_from,
            'leave_tenure_to' => $request->leave_tenure_to,
            'leave_type_id' => (int) $request->leave_type_id,
            'front_desk_leave_count' => $this->numberValue($request->front_desk_leave_count),
            'group_d_leave_count' => $this->numberValue($request->group_d_leave_count),
            'tsa_teacher_leave_count' => 0,
            'vhs_teacher_leave_count' => $this->numberValue($request->vhs_teacher_leave_count),
            'is_carry_forward' => (int) $request->input('is_carry_forward', 0) === 1,
        ];
    }

    private function numberValue($value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function leaveTypeOptions(int $selectedLeaveTypeId = 0)
    {
        return LeaveType::where(function ($query) use ($selectedLeaveTypeId) {
            $query->where('status', '=', 1);

            if ($selectedLeaveTypeId > 0) {
                $query->orWhere('id', '=', $selectedLeaveTypeId);
            }
        })
            ->where('status', '!=', 3)
            ->orderBy('name')
            ->get();
    }

    private function employeeCategoryValues($category): array
    {
        $decodedCategories = json_decode((string) $category, true);
        $categories = is_array($decodedCategories) ? $decodedCategories : [$category];

        $categories = array_map(function ($categoryValue) {
            return trim((string) $categoryValue);
        }, $categories);

        $categories = array_filter($categories, function ($categoryValue) {
            return $categoryValue !== '' && array_key_exists($categoryValue, self::CATEGORY_FIELDS);
        });

        return array_values(array_unique($categories));
    }

    private function employeeName($employee): string
    {
        return collect([
            $employee->first_name,
            $employee->middle_name,
            $employee->last_name,
        ])
            ->map(fn ($namePart) => trim((string) $namePart))
            ->filter()
            ->implode(' ');
    }

    private function currentUserId(): int
    {
        if (session()->has('user_data') && array_key_exists('user_id', session('user_data'))) {
            return (int) session('user_data')['user_id'];
        }

        return Auth::check() ? (int) Auth::id() : 0;
    }
}
