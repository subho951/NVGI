<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\EmployeeAttendanceAbsenceService;
use App\Services\SiteAuthService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeeAttendanceReportController extends Controller
{
    private const ATTENDANCE_TIMEZONE = 'Asia/Kolkata';

    private const ROSTER_CATEGORIES = [
        'VHS TEACHER',
        'TSA TEACHER',
        'FRONT-DESK',
        'GROUP-D',
    ];

    protected SiteAuthService $siteAuthService;

    protected EmployeeAttendanceAbsenceService $absenceService;

    public function __construct(EmployeeAttendanceAbsenceService $absenceService)
    {
        $this->siteAuthService = new SiteAuthService;
        $this->absenceService = $absenceService;
    }

    public function admin(Request $request)
    {
        [$fromDate, $toDate] = $this->resolveDateRange($request);
        $branchId = $this->positiveInt($request->input('branch_id'));
        $employeeId = $this->positiveInt($request->input('employee_id'));
        $branchIds = $branchId > 0 ? [$branchId] : [];
        $report = $this->reportMatrix($fromDate, $toDate, $branchIds, $employeeId);

        $data = [
            'module' => [
                'title' => 'Attendance Report',
                'controller_route' => 'employee/attendance-report',
            ],
            'selected_from_date' => $fromDate->toDateString(),
            'selected_to_date' => $toDate->toDateString(),
            'selected_period_label' => $this->periodLabel($fromDate, $toDate),
            'selected_branch_id' => $branchId,
            'selected_employee_id' => $employeeId,
            'branch_options' => Branch::select('id', 'name', 'serial_id')
                ->where('status', '=', 1)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->orderBy('serial_id')
                ->get(),
            'employee_options' => $this->employeeOptions($fromDate, $toDate, $branchIds),
            'report_dates' => $report['dates'],
            'rows' => $report['rows'],
            'stats' => $report['stats'],
        ];

        $data = $this->siteAuthService->admin_after_login_layout(
            'Employee Attendance Report',
            'employee.attendance-report',
            $data
        );

        return view('front.pages.employee.attendance-report', $data);
    }

    public function branch(Request $request)
    {
        $branch = $request->attributes->get('branch_portal');
        $centreBranchIds = $this->centreBranchIds($branch);
        [$fromDate, $toDate] = $this->resolveDateRange($request);
        $employeeId = $this->positiveInt($request->input('employee_id'));
        $report = $this->reportMatrix($fromDate, $toDate, $centreBranchIds, $employeeId);

        return view('front.pages.branch-portal.attendance.report', [
            'title' => 'Attendance Report',
            'branch' => $branch,
            'selected_from_date' => $fromDate->toDateString(),
            'selected_to_date' => $toDate->toDateString(),
            'selected_period_label' => $this->periodLabel($fromDate, $toDate),
            'selected_employee_id' => $employeeId,
            'employee_options' => $this->employeeOptions($fromDate, $toDate, $centreBranchIds),
            'report_dates' => $report['dates'],
            'rows' => $report['rows'],
            'stats' => $report['stats'],
        ]);
    }

    private function reportMatrix(Carbon $fromDate, Carbon $toDate, array $branchIds = [], int $employeeId = 0): array
    {
        $this->absenceService->sync($fromDate, $toDate, $branchIds);

        $query = DB::table('employee_schedule_rosters as roster')
            ->select([
                'roster.id as roster_id',
                'roster.employee_id',
                'roster.employee_no',
                'roster.employee_name',
                'roster.category',
                'roster.branch_id',
                'roster.branch_name',
                'roster.roster_date',
                'roster.in_time as scheduled_in_time',
                'roster.out_time as scheduled_out_time',
            ])
            ->whereIn('roster.category', self::ROSTER_CATEGORIES)
            ->whereBetween('roster.roster_date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->where('roster.status', '!=', 3);

        if (! empty($branchIds)) {
            $query->whereIn('roster.branch_id', $branchIds);
        }

        if ($employeeId > 0) {
            $query->where('roster.employee_id', '=', $employeeId);
        }

        $rosters = $query
            ->orderBy('roster.employee_name')
            ->orderBy('roster.roster_date')
            ->orderBy('roster.in_time')
            ->orderBy('roster.id')
            ->get();

        $attendances = DB::table('employee_attendances')
            ->whereIn('roster_id', $rosters->pluck('roster_id'))
            ->get()
            ->keyBy('roster_id');

        $entries = $rosters
            ->map(function ($row) use ($attendances) {
                $attendance = $attendances->get((int) $row->roster_id);
                $punchIn = $attendance && $attendance->punch_in_at
                    ? Carbon::parse($attendance->punch_in_at, self::ATTENDANCE_TIMEZONE)
                    : null;
                $punchOut = $attendance && $attendance->punch_out_at
                    ? Carbon::parse($attendance->punch_out_at, self::ATTENDANCE_TIMEZONE)
                    : null;
                $attendanceDate = Carbon::parse($row->roster_date, self::ATTENDANCE_TIMEZONE);
                $isAbsent = (bool) ($attendance->is_absent ?? false);
                $workedMinutes = ($punchIn && $punchOut)
                    ? (int) max(0, $punchIn->diffInMinutes($punchOut, false))
                    : null;

                return [
                    'roster_id' => (int) $row->roster_id,
                    'employee_id' => (int) $row->employee_id,
                    'employee_no' => (string) $row->employee_no,
                    'employee_name' => (string) $row->employee_name,
                    'category' => (string) $row->category,
                    'branch_id' => (int) $row->branch_id,
                    'branch_name' => (string) $row->branch_name,
                    'attendance_date_key' => $attendanceDate->toDateString(),
                    'scheduled_time' => $this->displayTimeRange(
                        $row->scheduled_in_time,
                        $row->scheduled_out_time
                    ),
                    'punch_in_time' => $punchIn ? $punchIn->format('h:i A') : '',
                    'punch_out_time' => $punchOut ? $punchOut->format('h:i A') : '',
                    'punch_in_image' => (string) ($attendance->punch_in_image ?? ''),
                    'punch_out_image' => (string) ($attendance->punch_out_image ?? ''),
                    'worked_time' => $this->displayDuration($workedMinutes),
                    'is_late' => (bool) ($attendance->is_late ?? false),
                    'late_minutes' => (int) ($attendance->late_minutes ?? 0),
                    'is_absent' => $isAbsent,
                    'status' => $this->attendanceStatus($attendanceDate, $punchIn, $punchOut, $isAbsent),
                ];
            });

        $dates = collect(CarbonPeriod::create($fromDate, $toDate))
            ->map(function (Carbon $date) {
                return [
                    'key' => $date->toDateString(),
                    'label' => $date->format('d-m-Y'),
                    'day' => $date->format('D'),
                ];
            })
            ->values();

        $rows = $entries
            ->groupBy('employee_id')
            ->map(function ($employeeEntries) {
                $first = $employeeEntries->first();

                return [
                    'employee_id' => $first['employee_id'],
                    'employee_no' => $first['employee_no'],
                    'employee_name' => $first['employee_name'],
                    'categories' => $employeeEntries->pluck('category')->filter()->unique()->values()->implode(', '),
                    'branches' => $employeeEntries->pluck('branch_name')->filter()->unique()->values()->implode(', '),
                    'late_count' => $employeeEntries->where('is_late', true)->count(),
                    'absent_count' => $employeeEntries->where('is_absent', true)->count(),
                    'days' => $employeeEntries
                        ->groupBy('attendance_date_key')
                        ->map(fn ($dayEntries) => $dayEntries->values())
                        ->all(),
                ];
            })
            ->sortBy(fn ($row) => Str::lower($row['employee_name']).'|'.$row['employee_no'])
            ->values();

        return [
            'dates' => $dates,
            'rows' => $rows,
            'stats' => [
                'employees' => $rows->count(),
                'scheduled' => $entries->count(),
                'completed' => $entries->where('status', 'completed')->count(),
                'working' => $entries->where('status', 'working')->count(),
                'absent' => $entries->where('status', 'absent')->count(),
                'pending' => $entries->where('status', 'pending')->count(),
                'late' => $entries->where('is_late', true)->count(),
            ],
        ];
    }

    private function employeeOptions(Carbon $fromDate, Carbon $toDate, array $branchIds = [])
    {
        $query = DB::table('employee_schedule_rosters')
            ->select('employee_id', 'employee_no', 'employee_name')
            ->whereIn('category', self::ROSTER_CATEGORIES)
            ->whereBetween('roster_date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->where('status', '!=', 3);

        if (! empty($branchIds)) {
            $query->whereIn('branch_id', $branchIds);
        }

        return $query
            ->distinct()
            ->orderBy('employee_name')
            ->orderBy('employee_no')
            ->get();
    }

    private function attendanceStatus(
        Carbon $attendanceDate,
        ?Carbon $punchIn,
        ?Carbon $punchOut,
        bool $isAbsent
    ): string {
        if ($punchOut) {
            return 'completed';
        }

        if ($punchIn) {
            return 'working';
        }

        return $isAbsent || $attendanceDate->lt(Carbon::today(self::ATTENDANCE_TIMEZONE))
            ? 'absent'
            : 'pending';
    }

    private function displayTimeRange($inTime, $outTime): string
    {
        $format = function ($time) {
            $time = trim((string) $time);

            try {
                return $time === ''
                    ? ''
                    : Carbon::createFromFormat('H:i', substr($time, 0, 5))->format('g:i A');
            } catch (\Throwable $e) {
                return $time;
            }
        };

        $inTime = $format($inTime);
        $outTime = $format($outTime);

        return trim($inTime.($inTime && $outTime ? ' - ' : '').$outTime);
    }

    private function displayDuration(?int $minutes): string
    {
        if ($minutes === null) {
            return '';
        }

        return intdiv($minutes, 60).'h '.($minutes % 60).'m';
    }

    private function resolveDateRange(Request $request): array
    {
        $today = Carbon::today(self::ATTENDANCE_TIMEZONE);
        $fromDate = $this->resolveDate($request->input('from_date'), $today);
        $toDate = $this->resolveDate($request->input('to_date'), $fromDate);

        if ($fromDate->greaterThan($toDate)) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        return [$fromDate->startOfDay(), $toDate->startOfDay()];
    }

    private function resolveDate($value, Carbon $fallback): Carbon
    {
        $value = trim((string) $value);

        try {
            return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)
                ? Carbon::createFromFormat('Y-m-d', $value, self::ATTENDANCE_TIMEZONE)
                : $fallback->copy();
        } catch (\Throwable $e) {
            return $fallback->copy();
        }
    }

    private function periodLabel(Carbon $fromDate, Carbon $toDate): string
    {
        return $fromDate->isSameDay($toDate)
            ? $fromDate->format('d M Y')
            : $fromDate->format('d M Y').' - '.$toDate->format('d M Y');
    }

    private function centreBranchIds($branch): array
    {
        return Branch::whereRaw('LOWER(name) = ?', [Str::lower(trim((string) $branch->name))])
            ->where('status', '=', 1)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->map(fn ($branchId) => (int) $branchId)
            ->values()
            ->all();
    }

    private function positiveInt($value): int
    {
        $value = (int) $value;

        return $value > 0 ? $value : 0;
    }
}
