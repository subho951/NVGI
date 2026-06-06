<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\SiteAuthService;
use Carbon\Carbon;
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

    public function __construct()
    {
        $this->siteAuthService = new SiteAuthService;
    }

    public function admin(Request $request)
    {
        $month = $this->resolveMonth($request->input('month', Carbon::now(self::ATTENDANCE_TIMEZONE)->format('Y-m')));
        $branchId = $this->positiveInt($request->input('branch_id'));
        $employeeId = $this->positiveInt($request->input('employee_id'));
        $branchIds = $branchId > 0 ? [$branchId] : [];
        $rows = $this->reportRows($month, $branchIds, $employeeId);

        $data = [
            'module' => [
                'title' => 'Attendance Report',
                'controller_route' => 'employee/attendance-report',
            ],
            'selected_month' => $month->format('Y-m'),
            'selected_month_label' => $month->format('F Y'),
            'selected_branch_id' => $branchId,
            'selected_employee_id' => $employeeId,
            'branch_options' => Branch::select('id', 'name', 'serial_id')
                ->where('status', '=', 1)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->orderBy('serial_id')
                ->get(),
            'employee_options' => $this->employeeOptions($month, $branchIds),
            'rows' => $rows,
            'stats' => $this->reportStats($rows),
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
        $month = $this->resolveMonth($request->input('month', Carbon::now(self::ATTENDANCE_TIMEZONE)->format('Y-m')));
        $employeeId = $this->positiveInt($request->input('employee_id'));
        $rows = $this->reportRows($month, $centreBranchIds, $employeeId);

        return view('front.pages.branch-portal.attendance.report', [
            'title' => 'Attendance Report',
            'branch' => $branch,
            'selected_month' => $month->format('Y-m'),
            'selected_month_label' => $month->format('F Y'),
            'selected_employee_id' => $employeeId,
            'employee_options' => $this->employeeOptions($month, $centreBranchIds),
            'rows' => $rows,
            'stats' => $this->reportStats($rows),
        ]);
    }

    private function reportRows(Carbon $month, array $branchIds = [], int $employeeId = 0)
    {
        $query = DB::table('employee_schedule_rosters as roster')
            ->leftJoin('employee_attendances as attendance', 'attendance.roster_id', '=', 'roster.id')
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
                'attendance.punch_in_at',
                'attendance.punch_in_image',
                'attendance.punch_out_at',
                'attendance.punch_out_image',
            ])
            ->whereIn('roster.category', self::ROSTER_CATEGORIES)
            ->whereBetween('roster.roster_date', [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ])
            ->where('roster.status', '!=', 3);

        if (! empty($branchIds)) {
            $query->whereIn('roster.branch_id', $branchIds);
        }

        if ($employeeId > 0) {
            $query->where('roster.employee_id', '=', $employeeId);
        }

        return $query
            ->orderBy('roster.roster_date')
            ->orderBy('roster.in_time')
            ->orderBy('roster.employee_name')
            ->orderBy('roster.id')
            ->get()
            ->map(function ($row) {
                $punchIn = $row->punch_in_at ? Carbon::parse($row->punch_in_at, self::ATTENDANCE_TIMEZONE) : null;
                $punchOut = $row->punch_out_at ? Carbon::parse($row->punch_out_at, self::ATTENDANCE_TIMEZONE) : null;
                $status = $this->attendanceStatus($row, $punchIn, $punchOut);
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
                    'attendance_date' => Carbon::parse($row->roster_date),
                    'scheduled_time' => $this->displayTimeRange($row->scheduled_in_time, $row->scheduled_out_time),
                    'punch_in_time' => $punchIn ? $punchIn->format('h:i A') : '',
                    'punch_out_time' => $punchOut ? $punchOut->format('h:i A') : '',
                    'punch_in_image' => (string) $row->punch_in_image,
                    'punch_out_image' => (string) $row->punch_out_image,
                    'worked_time' => $this->displayDuration($workedMinutes),
                    'status' => $status,
                    'status_label' => $this->statusLabel($status),
                ];
            });
    }

    private function employeeOptions(Carbon $month, array $branchIds = [])
    {
        $query = DB::table('employee_schedule_rosters')
            ->select('employee_id', 'employee_no', 'employee_name')
            ->whereIn('category', self::ROSTER_CATEGORIES)
            ->whereBetween('roster_date', [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ])
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

    private function reportStats($rows): array
    {
        return [
            'scheduled' => $rows->count(),
            'completed' => $rows->where('status', 'completed')->count(),
            'working' => $rows->where('status', 'working')->count(),
            'absent' => $rows->where('status', 'absent')->count(),
            'pending' => $rows->where('status', 'pending')->count(),
        ];
    }

    private function attendanceStatus($row, ?Carbon $punchIn, ?Carbon $punchOut): string
    {
        if ($punchOut) {
            return 'completed';
        }

        if ($punchIn) {
            return 'working';
        }

        return Carbon::parse($row->roster_date)->lt(Carbon::today(self::ATTENDANCE_TIMEZONE)) ? 'absent' : 'pending';
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'completed' => 'Completed',
            'working' => 'Punched In',
            'absent' => 'Absent',
            default => 'Not Marked',
        };
    }

    private function displayTimeRange($inTime, $outTime): string
    {
        $format = function ($time) {
            $time = trim((string) $time);

            return $time === ''
                ? ''
                : Carbon::createFromFormat('H:i', substr($time, 0, 5))->format('g:i A');
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

    private function resolveMonth($month): Carbon
    {
        $month = trim((string) $month);

        try {
            return preg_match('/^\d{4}-\d{2}$/', $month)
                ? Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfMonth()
                : Carbon::now(self::ATTENDANCE_TIMEZONE)->startOfMonth();
        } catch (\Throwable $e) {
            return Carbon::now(self::ATTENDANCE_TIMEZONE)->startOfMonth();
        }
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
