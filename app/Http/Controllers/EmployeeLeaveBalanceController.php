<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Employee;
use App\Models\EmployeeLeaveAllotment;
use App\Models\EmployeeLeaveTakenHistory;
use App\Services\SiteAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeLeaveBalanceController extends Controller
{
    protected $siteAuthService;
    protected $data;

    public function __construct()
    {
        $this->data = [
            'title' => 'Employee Leave Balance',
            'controller' => 'EmployeeLeaveBalanceController',
            'controller_route' => 'payroll-leave/employee-leave-balance',
            'primary_key' => 'id',
        ];

        $this->siteAuthService = new SiteAuthService();
    }

    public function list(Request $request)
    {
        $data['module'] = $this->data;
        $title = $this->data['title'].' List';
        $pageName = 'payroll-leave.employee-leave-balance.list';
        $balanceRows = DB::table('employee_leave_allotments as ela')
            ->leftJoin('leave_types as lt', 'lt.id', '=', 'ela.leave_type_id')
            ->select([
                'ela.employee_id',
                'ela.leave_type_id',
                DB::raw('MAX(ela.employee_no) as employee_no'),
                DB::raw('MAX(ela.employee_name) as employee_name'),
                DB::raw('MAX(ela.employee_category) as employee_category'),
                DB::raw('MAX(lt.name) as leave_type_name'),
                DB::raw('SUM(ela.total_allotment) as total_allotment'),
                DB::raw('SUM(ela.used_leave) as used_leave'),
                DB::raw('SUM(ela.balance_leave) as balance_leave'),
                DB::raw('MAX(ela.leave_tenure_to) as latest_tenure_to'),
            ])
            ->where('ela.status', '!=', 3)
            ->groupBy('ela.employee_id', 'ela.leave_type_id')
            ->orderBy('employee_name')
            ->orderBy('leave_type_name')
            ->get();

        $data['rows'] = $balanceRows
            ->groupBy('employee_id')
            ->map(function ($employeeRows) {
                $firstRow = $employeeRows->first();
                $leaveBalances = $employeeRows
                    ->sortBy(fn ($row) => $row->leave_type_name)
                    ->map(function ($row) {
                        return (object) [
                            'leave_type_id' => $row->leave_type_id,
                            'leave_type_name' => $row->leave_type_name,
                            'total_allotment' => (float) $row->total_allotment,
                            'used_leave' => (float) $row->used_leave,
                            'balance_leave' => (float) $row->balance_leave,
                        ];
                    })
                    ->values();

                return (object) [
                    'employee_id' => $firstRow->employee_id,
                    'employee_no' => $firstRow->employee_no,
                    'employee_name' => $firstRow->employee_name,
                    'employee_category' => $firstRow->employee_category,
                    'leave_balances' => $leaveBalances,
                    'total_balance' => (float) $leaveBalances->sum('balance_leave'),
                ];
            })
            ->sortBy(fn ($row) => trim(($row->employee_name ?: '').' '.($row->employee_no ?: '')))
            ->values();

        $data = $this->siteAuthService->admin_after_login_layout($title, $pageName, $data);

        return view('front.pages.'.$pageName, $data);
    }

    public function history(Request $request, $employee, $leaveType = null)
    {
        $employeeId = (int) Helper::decoded($employee);
        $selectedLeaveTypeId = $leaveType ? (int) Helper::decoded($leaveType) : null;
        $allotments = EmployeeLeaveAllotment::with('leaveType')
            ->where('employee_id', '=', $employeeId)
            ->where('status', '!=', 3)
            ->orderBy('leave_type_id')
            ->orderBy('leave_tenure_from')
            ->orderBy('id')
            ->get();

        if ($allotments->isEmpty()) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'Employee leave balance not found !!!');
        }

        $employeeRow = Employee::where('id', '=', $employeeId)->first();
        $firstAllotment = $allotments->first();
        $leaveTypeHistories = $allotments
            ->groupBy('leave_type_id')
            ->map(function ($typeAllotments, $leaveTypeId) use ($employeeId) {
                $takenRows = EmployeeLeaveTakenHistory::where('employee_id', '=', $employeeId)
                    ->where('leave_type_id', '=', $leaveTypeId)
                    ->where('status', '!=', 3)
                    ->orderBy('leave_date')
                    ->orderBy('id')
                    ->get();

                return [
                    'leave_type_id' => (int) $leaveTypeId,
                    'leave_type_name' => $typeAllotments->first()->leaveType->name ?? '--',
                    'summary' => [
                        'allotted' => (float) $typeAllotments->sum('total_allotment'),
                        'used' => (float) $typeAllotments->sum('used_leave'),
                        'balance' => (float) $typeAllotments->sum('balance_leave'),
                    ],
                    'passbookRows' => $this->passbookRows($typeAllotments, $takenRows),
                    'allotmentRows' => $typeAllotments,
                    'takenRows' => $takenRows,
                ];
            })
            ->sortBy('leave_type_name')
            ->values();

        if (! $selectedLeaveTypeId || ! $leaveTypeHistories->contains('leave_type_id', $selectedLeaveTypeId)) {
            $selectedLeaveTypeId = (int) $leaveTypeHistories->first()['leave_type_id'];
        }

        $data['module'] = $this->data;
        $data['employee'] = [
            'id' => $employeeId,
            'employee_no' => $employeeRow->employee_no ?? $firstAllotment->employee_no,
            'employee_name' => $employeeRow ? $this->employeeName($employeeRow) : $firstAllotment->employee_name,
            'category' => $firstAllotment->employee_category,
        ];
        $data['leaveTypeHistories'] = $leaveTypeHistories;
        $data['selectedLeaveTypeId'] = $selectedLeaveTypeId;

        $title = 'Leave History';
        $pageName = 'payroll-leave.employee-leave-balance.history';
        $data = $this->siteAuthService->admin_after_login_layout($title, $pageName, $data);

        return view('front.pages.'.$pageName, $data);
    }

    private function passbookRows($allotments, $takenRows)
    {
        $rows = [];
        $hasDetailedTakenRows = $takenRows->isNotEmpty();

        foreach ($allotments as $allotment) {
            if ((float) $allotment->current_allotment > 0) {
                $rows[] = [
                    'date' => $this->dateValue($allotment->leave_tenure_from),
                    'order' => 10,
                    'particulars' => 'Leave allotment ('.$this->dateLabel($allotment->leave_tenure_from).' to '.$this->dateLabel($allotment->leave_tenure_to).')',
                    'credit' => (float) $allotment->current_allotment,
                    'debit' => 0.0,
                ];
            }

            if ((float) $allotment->previous_balance > 0) {
                $rows[] = [
                    'date' => $this->dateValue($allotment->leave_tenure_from),
                    'order' => 20,
                    'particulars' => 'Carry forward balance',
                    'credit' => (float) $allotment->previous_balance,
                    'debit' => 0.0,
                ];
            }

            if (! $hasDetailedTakenRows && (float) $allotment->used_leave > 0) {
                $rows[] = [
                    'date' => $this->dateValue($allotment->updated_at ?: $allotment->leave_tenure_to),
                    'order' => 30,
                    'particulars' => 'Leave taken (recorded total)',
                    'credit' => 0.0,
                    'debit' => (float) $allotment->used_leave,
                ];
            }
        }

        foreach ($takenRows as $takenRow) {
            $rows[] = [
                'date' => $this->dateValue($takenRow->leave_date),
                'order' => 30,
                'particulars' => trim('Leave taken'.($takenRow->remarks ? ' - '.$takenRow->remarks : '')),
                'credit' => 0.0,
                'debit' => (float) $takenRow->leave_count,
            ];
        }

        usort($rows, function ($left, $right) {
            return [$left['date'], $left['order']] <=> [$right['date'], $right['order']];
        });

        $balance = 0.0;

        return collect($rows)->map(function ($row) use (&$balance) {
            $balance += (float) $row['credit'];
            $balance -= (float) $row['debit'];
            $row['balance'] = $balance;

            return $row;
        });
    }

    private function dateValue($value): string
    {
        return Carbon::parse($value)->toDateString();
    }

    private function dateLabel($value): string
    {
        return Carbon::parse($value)->format('d-m-Y');
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
}
