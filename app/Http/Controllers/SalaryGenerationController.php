<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\LeaveType;
use App\Models\SalaryGeneration;
use App\Models\SalaryHead;
use App\Services\EmployeeAttendanceAbsenceService;
use App\Services\SiteAuthService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SalaryGenerationController extends Controller
{
    protected $siteAuthService;
    protected $data;

    public function __construct()
    {
        $this->data = [
            'title' => 'Salary Generation',
            'controller' => 'SalaryGenerationController',
            'controller_route' => 'payroll-leave/salary-generation',
            'primary_key' => 'id',
        ];

        $this->siteAuthService = new SiteAuthService();
    }

    public function list(Request $request)
    {
        $data['module'] = $this->data;
        $title = $this->data['title'];
        $pageName = 'payroll-leave.salary-generation.list';
        $hasSearched = $request->has('search');

        if ($hasSearched) {
            $request->validate($this->filterRules());
        }

        $filters = $this->filtersFromRequest($request);
        $salaryHeads = $this->salaryHeads();
        $leaveTypes = $this->leaveTypes();

        $data['rows'] = $hasSearched ? $this->buildSearchRows($filters, $salaryHeads, null, $leaveTypes) : collect();
        $data['salaryHeads'] = $salaryHeads;
        $data['leaveTypes'] = $leaveTypes;
        $data['filters'] = $filters;
        $data['hasSearched'] = $hasSearched;
        $data['monthOptions'] = $this->monthOptions();
        $data['yearOptions'] = $this->yearOptions((int) $filters['year']);
        $data['branchOptions'] = $this->branchOptions();
        $data['categoryOptions'] = $this->categoryOptions();
        $data['isTsaCategory'] = $this->isTsaCategory($filters['category']);
        $data['action'] = 'List';

        $data = $this->siteAuthService->admin_after_login_layout($title, $pageName, $data);

        return view('front.pages.'.$pageName, $data);
    }

    public function generatedList(Request $request)
    {
        $data['module'] = $this->data;
        $title = 'Generated Salary List';
        $pageName = 'payroll-leave.salary-generation.generated-list';
        $data['rows'] = SalaryGeneration::where('status', '!=', 3)
            ->select([
                'salary_month',
                'salary_year',
                'branch_name',
                'employee_category',
                DB::raw('COUNT(*) as employee_count'),
                DB::raw('MAX(updated_at) as last_generated_at'),
            ])
            ->groupBy('salary_month', 'salary_year', 'branch_name', 'employee_category')
            ->orderByDesc('salary_year')
            ->orderByDesc('salary_month')
            ->orderBy('branch_name')
            ->orderBy('employee_category')
            ->get();
        $data['monthOptions'] = $this->monthOptions();
        $data['action'] = 'Generated List';

        $data = $this->siteAuthService->admin_after_login_layout($title, $pageName, $data);

        return view('front.pages.'.$pageName, $data);
    }

    public function downloadExcel(Request $request)
    {
        $request->validate($this->filterRules());
        $filters = $this->filtersFromRequest($request);
        $rows = SalaryGeneration::where('status', '!=', 3)
            ->where('salary_month', '=', (int) $filters['month'])
            ->where('salary_year', '=', (int) $filters['year'])
            ->where('branch_name', '=', $filters['branch_name'])
            ->where('employee_category', '=', $filters['category'])
            ->orderBy('employee_name')
            ->orderBy('employee_no')
            ->get();

        if ($rows->isEmpty()) {
            return redirect($this->data['controller_route'].'/generated-list')->with('error_message', 'No generated salary found for selected period !!!');
        }

        $salaryHeads = $this->salaryHeads();
        $leaveTypes = $this->leaveTypes();
        $html = view('front.pages.payroll-leave.salary-generation.excel', [
            'rows' => $rows,
            'salaryHeads' => $salaryHeads,
            'leaveTypes' => $leaveTypes,
            'filters' => $filters,
            'monthOptions' => $this->monthOptions(),
        ])->render();
        $fileName = 'salary-generation-'.$filters['year'].'-'.$filters['month'].'-'.$this->safeFilePart($filters['branch_name']).'-'.$this->safeFilePart($filters['category']).'.xls';

        return response("\xEF\xBB\xBF".$html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate($this->filterRules() + [
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', Rule::exists('employees', 'id')->where(function ($query) {
                $query->where('status', '=', 1);
            })],
        ]);

        $filters = $this->filtersFromRequest($request);
        $employeeIds = array_values(array_unique(array_map('intval', $request->input('employee_ids', []))));
        $salaryHeads = $this->salaryHeads();
        $leaveTypes = $this->leaveTypes();

        if ($salaryHeads->isEmpty()) {
            return redirect($this->data['controller_route'].'/list?'.$this->queryString($filters))->with('error_message', 'No active salary head found !!!');
        }

        $rows = $this->buildSearchRows($filters, $salaryHeads, $employeeIds, $leaveTypes);
        $readyRows = $rows->filter(fn ($row) => $row->is_ready)->values();

        if ($readyRows->isEmpty()) {
            return redirect($this->data['controller_route'].'/list?'.$this->queryString($filters))->with('error_message', 'No selected employee has complete calculated salary rows !!!');
        }

        $userId = $this->currentUserId();

        DB::transaction(function () use ($readyRows, $filters, $userId) {
            foreach ($readyRows as $row) {
                $clLeave = $row->leave['CL'] ?? ['alloted' => 0.0, 'balance' => 0.0];
                $mlLeave = $row->leave['ML'] ?? ['alloted' => 0.0, 'balance' => 0.0];

                SalaryGeneration::updateOrCreate(
                    [
                        'salary_month' => (int) $filters['month'],
                        'salary_year' => (int) $filters['year'],
                        'branch_name' => $filters['branch_name'],
                        'employee_id' => (int) $row->employee_id,
                        'employee_category' => $filters['category'],
                    ],
                    [
                        'employee_no' => $row->employee_no,
                        'employee_name' => $row->employee_name,
                        'employee_category' => $filters['category'],
                        'doj' => $row->doj,
                        'gross_salary' => $row->gross_salary,
                        'earning_total' => $row->earning_total,
                        'deduction_total' => $row->deduction_total,
                        'net_salary' => $row->net_salary,
                        'absent_days' => $row->absence['absent_days'],
                        'unpaid_absent_days' => $row->absence['unpaid_absent_days'],
                        'absent_amount' => $row->absence['amount'],
                        'assigned_hours' => $row->absence['assigned_hours'],
                        'attendance_hours' => $row->absence['attendance_hours'],
                        'late_count' => $row->absence['late_count'],
                        'late_penalty_units' => $row->absence['late_penalty_units'],
                        'cl_alloted' => $clLeave['alloted'],
                        'cl_balance' => $clLeave['balance'],
                        'ml_alloted' => $mlLeave['alloted'],
                        'ml_balance' => $mlLeave['balance'],
                        'leave_details' => json_encode($row->leave),
                        'salary_head_details' => json_encode(array_values($row->salary_head_details)),
                        'status' => 1,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]
                );
            }
        });

        $skippedCount = count($employeeIds) - $readyRows->count();
        $message = 'Salary generated for '.$readyRows->count().' employee(s).';

        if ($skippedCount > 0) {
            $message .= ' Skipped '.$skippedCount.' employee(s) without complete calculated salary.';
        }

        return redirect($this->data['controller_route'].'/generated-list')->with('success_message', $message);
    }

    private function buildSearchRows(array $filters, $salaryHeads, ?array $selectedEmployeeIds = null, $leaveTypes = null)
    {
        $branchIds = $this->branchIdsForName($filters['branch_name']);

        if (empty($branchIds)) {
            return collect();
        }

        $leaveTypes = $leaveTypes ?: $this->leaveTypes();
        $period = $this->salaryPeriod($filters);
        $this->syncAttendanceAbsence($period['start'], $period['end'], $branchIds);

        $employees = Employee::where('status', '=', 1)
            ->orderBy('first_name')
            ->orderBy('middle_name')
            ->orderBy('last_name')
            ->orderBy('employee_no')
            ->get()
            ->filter(function ($employee) use ($branchIds, $filters, $selectedEmployeeIds) {
                if (is_array($selectedEmployeeIds) && ! in_array((int) $employee->id, $selectedEmployeeIds, true)) {
                    return false;
                }

                $employeeBranchIds = $this->employeeBranchIds($employee->branch);
                $employeeCategories = $this->employeeCategoryValues($employee->category);

                return ! empty(array_intersect($employeeBranchIds, $branchIds))
                    && in_array($filters['category'], $employeeCategories, true);
            })
            ->values();

        if ($employees->isEmpty()) {
            return collect();
        }

        $employeeIds = $employees->pluck('id')->map(fn ($id) => (int) $id)->all();
        $salaryRowsMap = EmployeeSalary::where('status', '!=', 3)
            ->whereIn('employee_id', $employeeIds)
            ->where('employee_category', '=', $filters['category'])
            ->orderByRaw("FIELD(salary_head_type, 'EARNING', 'DEDUCTION')")
            ->orderBy('salary_head_name')
            ->get()
            ->groupBy('employee_id');
        $leaveMap = $this->leaveSnapshotMap($employeeIds, $filters, $leaveTypes);
        $attendanceMetricMap = $this->attendanceMetricMap($employeeIds, $filters, $branchIds);
        $generatedMap = SalaryGeneration::where('status', '!=', 3)
            ->where('salary_month', '=', (int) $filters['month'])
            ->where('salary_year', '=', (int) $filters['year'])
            ->where('branch_name', '=', $filters['branch_name'])
            ->where('employee_category', '=', $filters['category'])
            ->whereIn('employee_id', $employeeIds)
            ->get()
            ->keyBy('employee_id');

        return $employees->map(function ($employee) use ($salaryHeads, $salaryRowsMap, $leaveMap, $attendanceMetricMap, $generatedMap, $filters, $period, $leaveTypes) {
            $salaryRows = $salaryRowsMap->get($employee->id, collect());
            $salaryRowsByHead = $salaryRows->keyBy('salary_head_id');
            $grossSalary = $salaryRows->isNotEmpty()
                ? round((float) $salaryRows->first()->gross_salary, 2)
                : $this->employeeCategorySalary($employee, $filters['category']);
            $leaveSnapshot = $leaveMap[$employee->id] ?? $this->defaultLeaveSnapshot($leaveTypes);
            $attendanceMetric = $attendanceMetricMap[$employee->id] ?? $this->defaultAttendanceMetric();
            $absence = $this->absenceSnapshot($filters['category'], $grossSalary, $leaveSnapshot, $attendanceMetric, $period);
            $salaryValues = [];
            $salaryHeadDetails = [];
            $missingSalaryHeadCount = 0;

            foreach ($salaryHeads as $salaryHead) {
                $salaryRow = $salaryRowsByHead->get($salaryHead->id);
                $isAbsentHead = $this->isAbsentHead($salaryHead);
                $amount = $isAbsentHead
                    ? $absence['amount']
                    : ($salaryRow ? round((float) $salaryRow->calculated_amount, 2) : null);

                if ($amount === null) {
                    $missingSalaryHeadCount++;
                }

                $salaryValues[(int) $salaryHead->id] = $amount;

                if ($amount !== null) {
                    $salaryHeadDetails[] = [
                        'salary_head_id' => (int) $salaryHead->id,
                        'salary_head_name' => $salaryRow ? $salaryRow->salary_head_name : $salaryHead->name,
                        'salary_head_type' => $salaryRow ? $salaryRow->salary_head_type : $salaryHead->type,
                        'formula_label' => $isAbsentHead ? $absence['formula_label'] : ($salaryRow ? $salaryRow->formula_label : $salaryHead->formula_label),
                        'is_payslip_show' => $salaryRow ? ($salaryRow->is_payslip_show ? 1 : 0) : ($salaryHead->is_payslip_show ? 1 : 0),
                        'calculated_amount' => round((float) $amount, 2),
                    ];
                }
            }

            $earningTotal = 0.0;
            $deductionTotal = 0.0;

            foreach ($salaryHeads as $salaryHead) {
                $amount = $salaryValues[(int) $salaryHead->id] ?? null;

                if ($amount === null) {
                    continue;
                }

                if ($salaryHead->type === SalaryHead::TYPE_EARNING) {
                    $earningTotal += (float) $amount;
                } elseif ($salaryHead->type === SalaryHead::TYPE_DEDUCTION) {
                    $deductionTotal += (float) $amount;
                }
            }

            $generated = $generatedMap->get($employee->id);

            return (object) [
                'employee_id' => (int) $employee->id,
                'employee_no' => $employee->employee_no,
                'employee_name' => $this->employeeName($employee),
                'doj' => $employee->doj,
                'gross_salary' => $grossSalary,
                'earning_total' => round($earningTotal, 2),
                'deduction_total' => round($deductionTotal, 2),
                'net_salary' => round($earningTotal - $deductionTotal),
                'salary_values' => $salaryValues,
                'salary_head_details' => $salaryHeadDetails,
                'leave' => $leaveSnapshot,
                'absence' => $absence,
                'is_ready' => $salaryHeads->isNotEmpty() && $missingSalaryHeadCount === 0,
                'missing_salary_head_count' => $missingSalaryHeadCount,
                'generated_at' => $generated ? $generated->updated_at : null,
            ];
        });
    }

    private function salaryHeads()
    {
        return SalaryHead::where('status', '=', 1)
            ->orderByRaw("FIELD(type, 'EARNING', 'DEDUCTION')")
            ->orderBy('id')
            ->get();
    }

    private function leaveTypes()
    {
        return LeaveType::where('status', '=', 1)
            ->orderBy('id')
            ->get();
    }

    private function leaveSnapshotMap(array $employeeIds, array $filters, $leaveTypes): array
    {
        $period = $this->salaryPeriod($filters);
        $leaveRows = DB::table('employee_leave_allotments as ela')
            ->leftJoin('leave_types as lt', 'lt.id', '=', 'ela.leave_type_id')
            ->select([
                'ela.employee_id',
                DB::raw('UPPER(lt.name) as leave_code'),
                DB::raw('SUM(ela.total_allotment) as total_allotment'),
                DB::raw('SUM(ela.balance_leave) as balance_leave'),
            ])
            ->whereIn('ela.employee_id', $employeeIds)
            ->where('ela.status', '!=', 3)
            ->where('lt.status', '!=', 3)
            ->whereDate('ela.leave_tenure_from', '<=', $period['end']->toDateString())
            ->whereDate('ela.leave_tenure_to', '>=', $period['start']->toDateString())
            ->groupBy('ela.employee_id', DB::raw('UPPER(lt.name)'))
            ->get();
        $leaveMap = [];

        foreach ($leaveRows as $leaveRow) {
            $employeeId = (int) $leaveRow->employee_id;

            if (! isset($leaveMap[$employeeId])) {
                $leaveMap[$employeeId] = $this->defaultLeaveSnapshot($leaveTypes);
            }

            $leaveCode = strtoupper((string) $leaveRow->leave_code);
            $leaveMap[$employeeId][$leaveCode] = [
                'alloted' => round((float) $leaveRow->total_allotment, 2),
                'balance' => round((float) $leaveRow->balance_leave, 2),
            ];
        }

        return $leaveMap;
    }

    private function defaultLeaveSnapshot($leaveTypes = null): array
    {
        $leaveTypes = $leaveTypes ?: $this->leaveTypes();
        $snapshot = [];

        foreach ($leaveTypes as $leaveType) {
            $snapshot[strtoupper((string) $leaveType->name)] = [
                'alloted' => 0.0,
                'balance' => 0.0,
            ];
        }

        if (empty($snapshot)) {
            $snapshot = [
                'CL' => ['alloted' => 0.0, 'balance' => 0.0],
                'ML' => ['alloted' => 0.0, 'balance' => 0.0],
            ];
        }

        return $snapshot;
    }

    private function attendanceMetricMap(array $employeeIds, array $filters, array $branchIds): array
    {
        $period = $this->salaryPeriod($filters);
        $rows = DB::table('employee_schedule_rosters as roster')
            ->leftJoin('employee_attendances as attendance', 'attendance.roster_id', '=', 'roster.id')
            ->select([
                'roster.employee_id',
                'roster.roster_date',
                'roster.in_time',
                'roster.out_time',
                'attendance.is_absent',
                'attendance.is_late',
                'attendance.punch_in_at',
                'attendance.punch_out_at',
            ])
            ->whereIn('roster.employee_id', $employeeIds)
            ->whereIn('roster.branch_id', $branchIds)
            ->where('roster.category', '=', $filters['category'])
            ->where('roster.status', '!=', 3)
            ->whereBetween('roster.roster_date', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->orderBy('roster.employee_id')
            ->orderBy('roster.roster_date')
            ->get();
        $metricMap = [];

        foreach ($rows as $row) {
            $employeeId = (int) $row->employee_id;

            if (! isset($metricMap[$employeeId])) {
                $metricMap[$employeeId] = $this->defaultAttendanceMetric();
                $metricMap[$employeeId]['_absent_dates'] = [];
            }

            $metricMap[$employeeId]['assigned_hours'] += $this->scheduledHours($row->roster_date, $row->in_time, $row->out_time);
            $metricMap[$employeeId]['attendance_hours'] += $this->attendanceHours($row);

            if ((bool) $row->is_absent) {
                $metricMap[$employeeId]['_absent_dates'][(string) $row->roster_date] = true;
            }

            if ((bool) $row->is_late) {
                $metricMap[$employeeId]['late_count']++;
            }
        }

        foreach ($metricMap as $employeeId => $metric) {
            $metricMap[$employeeId]['assigned_hours'] = round((float) $metric['assigned_hours'], 2);
            $metricMap[$employeeId]['attendance_hours'] = round((float) $metric['attendance_hours'], 2);
            $metricMap[$employeeId]['absent_days'] = count($metric['_absent_dates']);
            unset($metricMap[$employeeId]['_absent_dates']);
        }

        return $metricMap;
    }

    private function defaultAttendanceMetric(): array
    {
        return [
            'assigned_hours' => 0.0,
            'attendance_hours' => 0.0,
            'absent_days' => 0.0,
            'late_count' => 0,
        ];
    }

    private function absenceSnapshot(string $category, float $grossSalary, array $leaveSnapshot, array $attendanceMetric, array $period): array
    {
        $assignedHours = round((float) ($attendanceMetric['assigned_hours'] ?? 0), 2);
        $attendanceHours = round((float) ($attendanceMetric['attendance_hours'] ?? 0), 2);
        $absentDays = round((float) ($attendanceMetric['absent_days'] ?? 0), 2);
        $lateCount = (int) ($attendanceMetric['late_count'] ?? 0);
        $latePenaltyUnits = (float) floor($lateCount / 3);

        if ($this->isTsaCategory($category)) {
            $hourlySalary = $assignedHours > 0 ? $grossSalary / $assignedHours : 0;
            $absentHours = max($assignedHours - min($attendanceHours, $assignedHours), 0);
            $totalAbsentHours = $assignedHours > 0 ? min($assignedHours, $absentHours + $latePenaltyUnits) : 0;
            $absentAmount = $hourlySalary * $totalAbsentHours;

            return [
                'amount' => round($absentAmount, 2),
                'absent_days' => $absentDays,
                'unpaid_absent_days' => 0.0,
                'assigned_hours' => $assignedHours,
                'attendance_hours' => $attendanceHours,
                'late_count' => $lateCount,
                'late_penalty_units' => $latePenaltyUnits,
                'formula_label' => '((Assigned Hour - Attendance Hour) + FLOOR(Late Count / 3)) x (Gross / Assigned Hour)',
            ];
        }

        $clBalance = (float) ($leaveSnapshot['CL']['balance'] ?? 0);
        $totalAbsentDays = $absentDays + $latePenaltyUnits;
        $unpaidAbsentDays = max($totalAbsentDays - $clBalance, 0);
        $perDaySalary = $period['days'] > 0 ? $grossSalary / $period['days'] : 0;

        return [
            'amount' => round($perDaySalary * $unpaidAbsentDays, 2),
            'absent_days' => round($totalAbsentDays, 2),
            'unpaid_absent_days' => round($unpaidAbsentDays, 2),
            'assigned_hours' => $assignedHours,
            'attendance_hours' => $attendanceHours,
            'late_count' => $lateCount,
            'late_penalty_units' => $latePenaltyUnits,
            'formula_label' => '(Gross / Month Days) x MAX((Absent Day + FLOOR(Late Count / 3)) - CL Balance, 0)',
        ];
    }

    private function scheduledHours($rosterDate, $inTime, $outTime): float
    {
        $inTime = substr(trim((string) $inTime), 0, 5);
        $outTime = substr(trim((string) $outTime), 0, 5);

        if ($inTime === '' || $outTime === '') {
            return 0.0;
        }

        try {
            $date = Carbon::parse($rosterDate)->toDateString();
            $start = Carbon::createFromFormat('Y-m-d H:i', $date.' '.$inTime);
            $end = Carbon::createFromFormat('Y-m-d H:i', $date.' '.$outTime);

            if ($end->lessThanOrEqualTo($start)) {
                $end->addDay();
            }

            return round(max(0, $start->diffInMinutes($end, false)) / 60, 2);
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    private function attendanceHours($row): float
    {
        if ((bool) $row->is_absent || empty($row->punch_in_at) || empty($row->punch_out_at)) {
            return 0.0;
        }

        try {
            $start = Carbon::parse($row->punch_in_at);
            $end = Carbon::parse($row->punch_out_at);

            if ($end->lessThanOrEqualTo($start)) {
                return 0.0;
            }

            return round($start->diffInMinutes($end, false) / 60, 2);
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    private function syncAttendanceAbsence(Carbon $periodStart, Carbon $periodEnd, array $branchIds): void
    {
        app(EmployeeAttendanceAbsenceService::class)->sync($periodStart, $periodEnd, $branchIds);
    }

    private function isAbsentHead(SalaryHead $salaryHead): bool
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower((string) $salaryHead->name)) === 'absent';
    }

    private function isTsaCategory(string $category): bool
    {
        return strtoupper(trim($category)) === 'TSA TEACHER';
    }

    private function salaryPeriod(array $filters): array
    {
        $start = Carbon::create((int) $filters['year'], (int) $filters['month'], 1)->startOfMonth();

        return [
            'start' => $start->copy(),
            'end' => $start->copy()->endOfMonth(),
            'days' => (int) $start->daysInMonth,
        ];
    }

    private function filterRules(): array
    {
        return [
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2026,2030'],
            'branch_name' => ['required', 'string', Rule::in($this->branchOptions())],
            'category' => ['required', 'string', Rule::in($this->categoryOptions())],
        ];
    }

    private function filtersFromRequest(Request $request): array
    {
        return [
            'month' => (int) $request->input('month', now()->month),
            'year' => (int) $request->input('year', now()->year),
            'branch_name' => trim((string) $request->input('branch_name', '')),
            'category' => trim((string) $request->input('category', '')),
        ];
    }

    private function queryString(array $filters): string
    {
        return http_build_query($filters + ['search' => 1]);
    }

    private function monthOptions(): array
    {
        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $months[$month] = Carbon::create(2000, $month, 1)->format('F');
        }

        return $months;
    }

    private function yearOptions(int $selectedYear): array
    {
        return range(2026, 2030);
    }

    private function branchOptions(): array
    {
        $branchNames = Branch::where('status', '!=', 3)
            ->orderBy('name')
            ->pluck('name')
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->values();
        $preferredBranchNames = collect(['Bibirhat', 'Mukundapur', 'Rajarhat'])
            ->filter(fn ($branchName) => $branchNames->contains($branchName))
            ->values();

        return ($preferredBranchNames->isNotEmpty() ? $preferredBranchNames : $branchNames)->all();
    }

    private function categoryOptions(): array
    {
        return [
            'VHS TEACHER',
            'TSA TEACHER',
            'FRONT-DESK',
            'GROUP-D',
        ];
    }

    private function branchIdsForName(string $branchName): array
    {
        return Branch::where('status', '!=', 3)
            ->where('name', '=', $branchName)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function employeeBranchIds($branch): array
    {
        if (! is_array($branch)) {
            $decoded = json_decode((string) $branch, true);
            $branch = is_array($decoded) ? $decoded : [];
        }

        return collect($branch)
            ->map(fn ($branchId) => (int) $branchId)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function employeeCategoryValues($category): array
    {
        $category = trim((string) $category);
        $decoded = json_decode($category, true);
        $categories = is_array($decoded) ? $decoded : preg_split('/\s*,\s*/', $category);

        return collect($categories)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function employeeCategorySalary($employee, string $category): float
    {
        $fallbackSalary = round((float) $employee->salary, 2);
        $categorySalaryMap = $this->employeeCategorySalaryMap($employee->category_salaries ?? null, $fallbackSalary);

        if ($category !== '' && array_key_exists($category, $categorySalaryMap)) {
            return $categorySalaryMap[$category];
        }

        return $fallbackSalary;
    }

    private function employeeCategorySalaryMap($categorySalaries, float $fallbackSalary): array
    {
        if (! is_array($categorySalaries)) {
            $decodedSalaryValues = json_decode((string) $categorySalaries, true);
            $categorySalaries = is_array($decodedSalaryValues) ? $decodedSalaryValues : [];
        }

        return collect($categorySalaries)
            ->mapWithKeys(function ($salary, $category) use ($fallbackSalary) {
                $category = trim((string) $category);

                if ($category === '') {
                    return [];
                }

                return [$category => round((float) ($salary === '' ? $fallbackSalary : $salary), 2)];
            })
            ->all();
    }

    private function employeeName($employee): string
    {
        $name = collect([
            $employee->first_name,
            $employee->middle_name,
            $employee->last_name,
        ])
            ->map(fn ($namePart) => trim((string) $namePart))
            ->filter()
            ->implode(' ');

        return $name !== '' ? $name : (string) $employee->employee_no;
    }

    private function safeFilePart(string $value): string
    {
        $value = preg_replace('/[^a-z0-9]+/i', '-', trim($value));

        return trim((string) $value, '-') ?: 'salary';
    }

    private function currentUserId(): int
    {
        if (session()->has('user_data') && array_key_exists('user_id', session('user_data'))) {
            return (int) session('user_data')['user_id'];
        }

        return Auth::check() ? (int) Auth::id() : 0;
    }
}
