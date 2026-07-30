<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Employee;
use App\Models\EmployeeLeaveAllotment;
use App\Models\EmployeeLeaveTakenHistory;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Role;
use App\Services\SiteAuthService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LeaveApplicationController extends Controller
{
    private const STATUS_PENDING = 0;
    private const STATUS_APPROVED = 1;
    private const STATUS_REJECTED = 2;
    private const LEAVE_NOTIFICATION_RECIPIENT = 'subhomoysamanta1989@gmail.com';
    private const NVGI_LOGO_URL = 'https://newvedantgroup.com/nvgi/public/uploads/1775890427png-logo.png';

    protected $siteAuthService;
    protected $data;

    public function __construct()
    {
        $this->data = [
            'title' => 'Leave Application',
            'controller' => 'LeaveApplicationController',
            'controller_route' => 'payroll-leave/leave-application',
            'primary_key' => 'id',
        ];

        $this->siteAuthService = new SiteAuthService();
    }

    public function list(Request $request)
    {
        $data['module'] = $this->data;
        $title = $this->data['title'].' List';
        $pageName = 'payroll-leave.leave-application.list';
        $data['rows'] = LeaveApplication::where('status', '!=', 3)
            ->orderBy('id', 'DESC')
            ->get();
        $data['canCreateApplication'] = $this->canCreateApplication();
        $data['canApproveApplication'] = $this->canApproveApplication();
        $data['statusLabels'] = $this->statusLabels();
        $data['action'] = 'List';

        $data = $this->siteAuthService->admin_after_login_layout($title, $pageName, $data);

        return view('front.pages.'.$pageName, $data);
    }

    public function add(Request $request)
    {
        if (! $this->canCreateApplication()) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'You are not allowed to create leave application !!!');
        }

        $clLeaveType = $this->clLeaveType();
        if (! $clLeaveType) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'CL leave type not found !!!');
        }

        $data = $this->formData('Add', null, $clLeaveType);

        if ($request->isMethod('post')) {
            $request->validate($this->validationRules($clLeaveType));
            $application = DB::transaction(function () use ($request, $clLeaveType) {
                $employee = $this->employeeForApplication(
                    (int) $request->employee_id,
                    (int) $clLeaveType->id,
                    true
                );
                $this->validateApplicationAvailability(
                    $employee,
                    $clLeaveType,
                    (string) $request->leave_from_date,
                    (string) $request->leave_to_date
                );

                return LeaveApplication::create($this->payload($request, $employee, $clLeaveType) + [
                    'application_status' => self::STATUS_PENDING,
                    'status' => 1,
                    'created_by' => $this->currentUserId(),
                    'updated_by' => $this->currentUserId(),
                ]);
            });

            $this->sendLeaveApplicationEmail($application, 'applied');

            return redirect($this->data['controller_route'].'/list')->with('success_message', $this->data['title'].' added successfully !!!');
        }

        $data = $this->siteAuthService->admin_after_login_layout($data['title'], $data['pageName'], $data);

        return view('front.pages.'.$data['pageName'], $data);
    }

    public function edit(Request $request, $id)
    {
        if (! $this->canCreateApplication()) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'You are not allowed to edit leave application !!!');
        }

        $id = Helper::decoded($id);
        $row = LeaveApplication::where($this->data['primary_key'], '=', $id)
            ->where('status', '!=', 3)
            ->first();

        if (! $row) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'Leave application not found !!!');
        }

        if ((int) $row->application_status !== self::STATUS_PENDING) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'Only pending leave application can be edited !!!');
        }

        $clLeaveType = $this->clLeaveType((int) $row->leave_type_id);
        if (! $clLeaveType) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'CL leave type not found !!!');
        }

        $data = $this->formData('Edit', $row, $clLeaveType);

        if ($request->isMethod('post')) {
            $request->validate($this->validationRules($clLeaveType));
            DB::transaction(function () use ($request, $clLeaveType, $row) {
                $lockedRow = LeaveApplication::where('id', '=', (int) $row->id)
                    ->where('application_status', '=', self::STATUS_PENDING)
                    ->lockForUpdate()
                    ->firstOrFail();
                $employee = $this->employeeForApplication(
                    (int) $request->employee_id,
                    (int) $clLeaveType->id,
                    true
                );
                $this->validateApplicationAvailability(
                    $employee,
                    $clLeaveType,
                    (string) $request->leave_from_date,
                    (string) $request->leave_to_date,
                    (int) $lockedRow->id
                );

                $lockedRow->update($this->payload($request, $employee, $clLeaveType) + [
                    'updated_by' => $this->currentUserId(),
                ]);
            });

            return redirect($this->data['controller_route'].'/list')->with('success_message', $this->data['title'].' updated successfully !!!');
        }

        $data = $this->siteAuthService->admin_after_login_layout($data['title'], $data['pageName'], $data);

        return view('front.pages.'.$data['pageName'], $data);
    }

    public function approve(Request $request, $id)
    {
        if (! $this->canApproveApplication()) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'Only Master Admin can approve leave application !!!');
        }

        try {
            $applicationId = (int) Helper::decoded($id);
            $application = DB::transaction(function () use ($applicationId) {
                $application = LeaveApplication::where($this->data['primary_key'], '=', $applicationId)
                    ->where('status', '!=', 3)
                    ->lockForUpdate()
                    ->first();

                if (! $application || (int) $application->application_status !== self::STATUS_PENDING) {
                    throw ValidationException::withMessages([
                        'leave_application' => 'This leave application is no longer pending.',
                    ]);
                }

                $allotment = $this->employeeLeaveAllotmentForApplication($application, true);
                if (! $allotment) {
                    throw ValidationException::withMessages([
                        'leave_application' => 'No valid leave allotment covers the requested leave period.',
                    ]);
                }

                $leaveDays = (float) $application->no_of_days;
                $availableLeave = $this->availableLeaveBalance($allotment);
                if ($availableLeave < $leaveDays) {
                    throw ValidationException::withMessages([
                        'leave_application' => 'Insufficient leave balance. Available: '
                            .$this->formatLeaveCount($availableLeave)
                            .', requested: '.$this->formatLeaveCount($leaveDays).'.',
                    ]);
                }

                $history = EmployeeLeaveTakenHistory::create([
                    'leave_application_id' => (int) $application->id,
                    'employee_id' => (int) $application->employee_id,
                    'employee_no' => $application->employee_no,
                    'employee_name' => $application->employee_name,
                    'leave_type_id' => (int) $application->leave_type_id,
                    'leave_date' => $application->leave_from_date,
                    'leave_count' => (float) $application->no_of_days,
                    'remarks' => $this->historyRemarks($application),
                    'status' => 1,
                    'created_by' => $this->currentUserId(),
                    'updated_by' => $this->currentUserId(),
                ]);

                $this->deductEmployeeLeaveBalance($application, $allotment);

                $application->update([
                    'application_status' => self::STATUS_APPROVED,
                    'approved_at' => now(),
                    'approved_by' => $this->currentUserId(),
                    'leave_taken_history_id' => $history->id,
                    'updated_by' => $this->currentUserId(),
                ]);

                return $application;
            });

            $application->refresh();
            $this->sendLeaveApplicationEmail($application, 'approved');
        } catch (ValidationException $e) {
            return redirect($this->data['controller_route'].'/list')
                ->with('error_message', collect($e->errors())->flatten()->first() ?: 'Unable to approve leave application !!!');
        } catch (\Throwable $e) {
            report($e);

            return redirect($this->data['controller_route'].'/list')->with('error_message', 'Unable to approve leave application !!!');
        }

        return redirect($this->data['controller_route'].'/list')->with('success_message', 'Leave application approved successfully !!!');
    }

    public function reject(Request $request, $id)
    {
        if (! $this->canApproveApplication()) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'Only Master Admin can reject leave application !!!');
        }

        $request->validate([
            'reject_reason' => ['required', 'string', 'max:1000'],
        ]);

        $application = $this->pendingApplication($id);
        if (! $application) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'Pending leave application not found !!!');
        }

        $application->update([
            'application_status' => self::STATUS_REJECTED,
            'rejected_at' => now(),
            'rejected_by' => $this->currentUserId(),
            'reject_reason' => $this->nullableText($request->reject_reason),
            'updated_by' => $this->currentUserId(),
        ]);

        $application->refresh();
        $this->sendLeaveApplicationEmail($application, 'rejected');

        return redirect($this->data['controller_route'].'/list')->with('success_message', 'Leave application rejected successfully !!!');
    }

    private function formData(string $action, $row, LeaveType $clLeaveType): array
    {
        return [
            'module' => $this->data,
            'title' => $this->data['title'].' '.$action,
            'pageName' => 'payroll-leave.leave-application.add-edit',
            'row' => $row,
            'action' => $action,
            'employees' => $this->employeesWithLeaveAllotment((int) $clLeaveType->id),
            'clLeaveType' => $clLeaveType,
        ];
    }

    private function validationRules(LeaveType $clLeaveType): array
    {
        return [
            'employee_id' => [
                'required',
                'integer',
                Rule::exists('employees', 'id')->where(function ($query) {
                    $query->where('status', '=', 1);
                }),
                Rule::exists('employee_leave_allotments', 'employee_id')->where(function ($query) use ($clLeaveType) {
                    $query
                        ->where('leave_type_id', '=', (int) $clLeaveType->id)
                        ->where('status', '!=', 3);
                }),
            ],
            'leave_from_date' => ['required', 'date'],
            'leave_to_date' => ['required', 'date', 'after_or_equal:leave_from_date'],
            'no_of_days' => ['nullable', 'numeric', 'min:1', 'max:365'],
            'apply_date' => ['required', 'date', 'before_or_equal:today'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function payload(Request $request, Employee $employee, LeaveType $clLeaveType): array
    {
        $categories = $this->employeeCategoryValues($employee->category);

        return [
            'employee_id' => (int) $employee->id,
            'employee_no' => $employee->employee_no,
            'employee_name' => $this->employeeName($employee),
            'employee_category' => implode(', ', $categories),
            'leave_type_id' => (int) $clLeaveType->id,
            'leave_type_name' => $clLeaveType->name,
            'leave_from_date' => $request->leave_from_date,
            'leave_to_date' => $request->leave_to_date,
            'no_of_days' => $this->calculatedLeaveDays(
                (string) $request->leave_from_date,
                (string) $request->leave_to_date
            ),
            'apply_date' => $request->apply_date,
            'remarks' => $this->nullableText($request->remarks),
        ];
    }

    private function deductEmployeeLeaveBalance(
        LeaveApplication $application,
        EmployeeLeaveAllotment $allotment
    ): void
    {
        $usedLeave = (float) EmployeeLeaveTakenHistory::where(
            'employee_id',
            '=',
            (int) $allotment->employee_id
        )
            ->where('leave_type_id', '=', (int) $allotment->leave_type_id)
            ->where('status', '!=', 3)
            ->whereBetween('leave_date', [
                Carbon::parse($allotment->leave_tenure_from)->toDateString(),
                Carbon::parse($allotment->leave_tenure_to)->toDateString(),
            ])
            ->sum('leave_count');
        $allotment->used_leave = $usedLeave;
        $allotment->balance_leave = max((float) $allotment->total_allotment - $usedLeave, 0);
        $allotment->save();
    }

    private function employeeLeaveAllotmentForApplication(
        LeaveApplication $application,
        bool $lock = false
    ): ?EmployeeLeaveAllotment {
        $query = EmployeeLeaveAllotment::where('employee_id', '=', (int) $application->employee_id)
            ->where('leave_type_id', '=', (int) $application->leave_type_id)
            ->where('status', '!=', 3)
            ->whereDate('leave_tenure_from', '<=', Carbon::parse($application->leave_from_date)->toDateString())
            ->whereDate('leave_tenure_to', '>=', Carbon::parse($application->leave_to_date)->toDateString())
            ->orderBy('leave_tenure_to', 'DESC')
            ->orderBy('id', 'DESC');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function validateNoOverlappingApplication(
        int $employeeId,
        int $leaveTypeId,
        string $leaveFromDate,
        string $leaveToDate,
        int $excludeApplicationId = 0
    ): void {
        $query = LeaveApplication::where('employee_id', '=', $employeeId)
            ->where('leave_type_id', '=', $leaveTypeId)
            ->where('status', '!=', 3)
            ->whereIn('application_status', [
                self::STATUS_PENDING,
                self::STATUS_APPROVED,
            ])
            ->whereDate('leave_from_date', '<=', Carbon::parse($leaveToDate)->toDateString())
            ->whereDate('leave_to_date', '>=', Carbon::parse($leaveFromDate)->toDateString());

        if ($excludeApplicationId > 0) {
            $query->where('id', '!=', $excludeApplicationId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'leave_from_date' => 'This employee already has a pending or approved leave application overlapping the selected dates.',
            ]);
        }
    }

    private function validateApplicationAvailability(
        Employee $employee,
        LeaveType $leaveType,
        string $leaveFromDate,
        string $leaveToDate,
        int $excludeApplicationId = 0
    ): void {
        $this->validateNoOverlappingApplication(
            (int) $employee->id,
            (int) $leaveType->id,
            $leaveFromDate,
            $leaveToDate,
            $excludeApplicationId
        );

        $allotment = EmployeeLeaveAllotment::where('employee_id', '=', (int) $employee->id)
            ->where('leave_type_id', '=', (int) $leaveType->id)
            ->where('status', '!=', 3)
            ->whereDate('leave_tenure_from', '<=', Carbon::parse($leaveFromDate)->toDateString())
            ->whereDate('leave_tenure_to', '>=', Carbon::parse($leaveToDate)->toDateString())
            ->lockForUpdate()
            ->first();

        if (! $allotment) {
            throw ValidationException::withMessages([
                'leave_from_date' => 'No leave allotment covers the complete requested period.',
            ]);
        }

        $requestedLeave = $this->calculatedLeaveDays($leaveFromDate, $leaveToDate);
        $availableLeave = $this->availableLeaveBalance($allotment);

        if ($availableLeave < $requestedLeave) {
            throw ValidationException::withMessages([
                'leave_from_date' => 'Insufficient leave balance. Available: '
                    .$this->formatLeaveCount($availableLeave)
                    .', requested: '.$this->formatLeaveCount($requestedLeave).'.',
            ]);
        }
    }

    private function availableLeaveBalance(EmployeeLeaveAllotment $allotment): float
    {
        $usedLeave = (float) EmployeeLeaveTakenHistory::where(
            'employee_id',
            '=',
            (int) $allotment->employee_id
        )
            ->where('leave_type_id', '=', (int) $allotment->leave_type_id)
            ->where('status', '!=', 3)
            ->whereBetween('leave_date', [
                Carbon::parse($allotment->leave_tenure_from)->toDateString(),
                Carbon::parse($allotment->leave_tenure_to)->toDateString(),
            ])
            ->sum('leave_count');

        return round(max((float) $allotment->total_allotment - $usedLeave, 0), 2);
    }

    private function calculatedLeaveDays(string $leaveFromDate, string $leaveToDate): float
    {
        $fromDate = Carbon::parse($leaveFromDate)->startOfDay();
        $toDate = Carbon::parse($leaveToDate)->startOfDay();

        return (float) ($fromDate->diffInDays($toDate) + 1);
    }

    private function formatLeaveCount($value): string
    {
        $formatted = number_format((float) $value, 2, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }

    private function pendingApplication($id)
    {
        return LeaveApplication::where($this->data['primary_key'], '=', Helper::decoded($id))
            ->where('status', '!=', 3)
            ->where('application_status', '=', self::STATUS_PENDING)
            ->first();
    }

    private function clLeaveType(int $fallbackId = 0)
    {
        return LeaveType::where('status', '!=', 3)
            ->where(function ($query) use ($fallbackId) {
                $query->whereRaw('LOWER(name) = ?', ['cl']);

                if ($fallbackId > 0) {
                    $query->orWhere('id', '=', $fallbackId);
                }
            })
            ->orderByRaw("LOWER(name) = 'cl' DESC")
            ->first();
    }

    private function employeeForApplication(int $employeeId, int $leaveTypeId, bool $lock = false): Employee
    {
        $query = Employee::where('id', '=', $employeeId)
            ->where('status', '=', 1)
            ->whereIn('id', function ($query) use ($leaveTypeId) {
                $query
                    ->select('employee_id')
                    ->from('employee_leave_allotments')
                    ->where('leave_type_id', '=', $leaveTypeId)
                    ->where('status', '!=', 3);
            });

        if ($lock) {
            $query->lockForUpdate();
        }

        $employee = $query->firstOrFail();

        if (! $this->employeeHasLeaveEligibleCategory($employee)) {
            throw ValidationException::withMessages([
                'employee_id' => 'TSA Teacher employees do not have allotted leave.',
            ]);
        }

        return $employee;
    }

    private function employeesWithLeaveAllotment(int $leaveTypeId)
    {
        $balanceMap = $this->employeeLeaveBalanceMap($leaveTypeId);

        return Employee::where('status', '=', 1)
            ->whereIn('id', function ($query) use ($leaveTypeId) {
                $query
                    ->select('employee_id')
                    ->from('employee_leave_allotments')
                    ->where('leave_type_id', '=', $leaveTypeId)
                    ->where('status', '!=', 3);
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->filter(fn ($employee) => $this->employeeHasLeaveEligibleCategory($employee))
            ->values()
            ->map(function ($employee) use ($balanceMap) {
                $employee->cl_balance = (float) ($balanceMap[$employee->id] ?? 0);

                return $employee;
            });
    }

    private function employeeLeaveBalanceMap(int $leaveTypeId): array
    {
        return EmployeeLeaveAllotment::select('employee_id', DB::raw('SUM(balance_leave) as balance_leave'))
            ->where('leave_type_id', '=', $leaveTypeId)
            ->where('status', '!=', 3)
            ->groupBy('employee_id')
            ->pluck('balance_leave', 'employee_id')
            ->map(fn ($balance) => (float) $balance)
            ->toArray();
    }

    private function statusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    private function sendLeaveApplicationEmail(LeaveApplication $application, string $mailType): void
    {
        $mailConfig = [
            'applied' => [
                'view' => 'mails.leave-application.applied',
                'subject' => 'NVGI Leave Application Submitted - '.$application->employee_name,
            ],
            'approved' => [
                'view' => 'mails.leave-application.approved',
                'subject' => 'NVGI Leave Application Approved - '.$application->employee_name,
            ],
            'rejected' => [
                'view' => 'mails.leave-application.rejected',
                'subject' => 'NVGI Leave Application Rejected - '.$application->employee_name,
            ],
        ];

        if (! array_key_exists($mailType, $mailConfig)) {
            return;
        }

        try {
            $message = view($mailConfig[$mailType]['view'], [
                'application' => $application,
                'logoUrl' => self::NVGI_LOGO_URL,
                'statusLabels' => $this->statusLabels(),
            ])->render();

            $this->sendMail($this->leaveNotificationRecipient($application), $mailConfig[$mailType]['subject'], $message);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function leaveNotificationRecipient(LeaveApplication $application): string
    {
        return self::LEAVE_NOTIFICATION_RECIPIENT;
    }

    private function historyRemarks(LeaveApplication $application): string
    {
        $dateRange = Carbon::parse($application->leave_from_date)->format('d-m-Y').' to '.Carbon::parse($application->leave_to_date)->format('d-m-Y');
        $remarks = trim((string) $application->remarks);

        return trim('Leave application #'.$application->id.' approved ('.$dateRange.')'.($remarks !== '' ? ' - '.$remarks : ''));
    }

    private function canCreateApplication(): bool
    {
        return $this->isCurrentRole('Academic') || $this->isCurrentRole('Master Admin');
    }

    private function canApproveApplication(): bool
    {
        return $this->isCurrentRole('Master Admin');
    }

    private function isCurrentRole(string $expectedRole): bool
    {
        $expectedRole = strtolower(trim($expectedRole));
        $roleName = strtolower(trim($this->currentRoleName()));

        if ($roleName === $expectedRole) {
            return true;
        }

        $roleId = (int) (session('user_data')['role_id'] ?? 0);

        return ($expectedRole === 'master admin' && $roleId === 1)
            || ($expectedRole === 'academic' && $roleId === 6);
    }

    private function currentRoleName(): string
    {
        $roleId = (int) (session('user_data')['role_id'] ?? 0);

        return (string) Role::where('id', '=', $roleId)->value('name');
    }

    private function employeeCategoryValues($category): array
    {
        $decodedCategories = json_decode((string) $category, true);
        $categories = is_array($decodedCategories) ? $decodedCategories : [$category];

        $categories = array_map(function ($categoryValue) {
            return trim((string) $categoryValue);
        }, $categories);

        $categories = array_filter($categories, function ($categoryValue) {
            return $categoryValue !== '';
        });

        return array_values(array_unique($categories));
    }

    private function employeeHasLeaveEligibleCategory(Employee $employee): bool
    {
        return ! empty(array_intersect(
            $this->employeeCategoryValues($employee->category),
            ['VHS TEACHER', 'FRONT-DESK', 'GROUP-D']
        ));
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

    private function nullableText($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function currentUserId(): int
    {
        if (session()->has('user_data') && array_key_exists('user_id', session('user_data'))) {
            return (int) session('user_data')['user_id'];
        }

        return Auth::check() ? (int) Auth::id() : 0;
    }
}
