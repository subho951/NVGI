<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeScheduleRoster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BranchAttendanceController extends Controller
{
    private const ATTENDANCE_TIMEZONE = 'Asia/Kolkata';

    private const ROSTER_CATEGORIES = [
        'VHS TEACHER',
        'TSA TEACHER',
        'FRONT-DESK',
        'GROUP-D',
    ];

    public function index(Request $request)
    {
        $branch = $request->attributes->get('branch_portal');
        $centreBranchIds = $this->centreBranchIds($branch);
        $today = Carbon::today(self::ATTENDANCE_TIMEZONE);

        $rosters = EmployeeScheduleRoster::whereIn('branch_id', $centreBranchIds)
            ->whereIn('category', self::ROSTER_CATEGORIES)
            ->whereDate('roster_date', '=', $today->toDateString())
            ->where('status', '!=', 3)
            ->orderBy('in_time', 'ASC')
            ->orderBy('employee_name', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();

        $attendances = EmployeeAttendance::whereIn('roster_id', $rosters->pluck('id'))
            ->get()
            ->keyBy('roster_id');

        $employees = Employee::whereIn('id', $rosters->pluck('employee_id')->unique())
            ->get()
            ->keyBy('id');

        $branchCodes = Branch::whereIn('id', $rosters->pluck('branch_id')->unique())
            ->pluck('serial_id', 'id');

        $rows = $rosters
            ->map(function ($roster) use ($attendances, $employees, $branchCodes) {
                $attendance = $attendances->get((int) $roster->id);
                $employee = $employees->get((int) $roster->employee_id);
                $state = $this->attendanceState($attendance);

                return [
                    'roster_id' => (int) $roster->id,
                    'employee_id' => (int) $roster->employee_id,
                    'employee_no' => (string) $roster->employee_no,
                    'employee_name' => (string) $roster->employee_name,
                    'category' => (string) $roster->category,
                    'branch_id' => (int) $roster->branch_id,
                    'branch_name' => (string) $roster->branch_name,
                    'branch_code' => (string) ($branchCodes[(int) $roster->branch_id] ?? ''),
                    'scheduled_time' => $this->displayTimeRange($roster->in_time, $roster->out_time),
                    'scheduled_in_time' => (string) $roster->in_time,
                    'employee_image' => (string) ($employee->image ?? ''),
                    'gender' => (string) ($employee->gender ?? ''),
                    'state' => $state,
                    'state_label' => $this->stateLabel($state),
                    'action_label' => $state === 'punched_in' ? 'Punch Out' : 'Punch In',
                    'punch_in_time' => $attendance && $attendance->punch_in_at
                        ? $attendance->punch_in_at->format('h:i A')
                        : '',
                    'punch_out_time' => $attendance && $attendance->punch_out_at
                        ? $attendance->punch_out_at->format('h:i A')
                        : '',
                    'mark_url' => $state === 'completed'
                        ? ''
                        : route('branch.portal.attendance.mark', ['roster' => $roster->id]),
                ];
            })
            ->sortBy(function ($row) {
                $stateOrder = [
                    'punched_in' => 0,
                    'pending' => 1,
                    'completed' => 2,
                ];

                return sprintf(
                    '%d|%s|%s|%010d',
                    $stateOrder[$row['state']] ?? 9,
                    $row['scheduled_in_time'],
                    Str::lower($row['employee_name']),
                    $row['roster_id']
                );
            })
            ->values();

        return view('front.pages.branch-portal.attendance.index', [
            'title' => 'Today Attendance',
            'branch' => $branch,
            'attendance_date' => $today,
            'rows' => $rows,
            'is_mobile_device' => $this->isMobileRequest($request),
            'stats' => [
                'scheduled' => $rows->count(),
                'pending' => $rows->where('state', 'pending')->count(),
                'punched_in' => $rows->where('state', 'punched_in')->count(),
                'completed' => $rows->where('state', 'completed')->count(),
            ],
        ]);
    }

    public function mark(Request $request, int $roster)
    {
        $rosterRow = $this->accessibleRoster($request, $roster);
        if (! $rosterRow) {
            return redirect()
                ->route('branch.portal.attendance.index')
                ->with('error_message', 'This roster assignment is not available for attendance at this branch today.');
        }

        $attendance = EmployeeAttendance::where('roster_id', '=', $rosterRow->id)->first();
        if ($attendance && $attendance->punch_out_at) {
            return redirect()
                ->route('branch.portal.attendance.index')
                ->with('success_message', $rosterRow->employee_name.' has already completed attendance for this class.');
        }

        $mode = $attendance && $attendance->punch_in_at ? 'punch_out' : 'punch_in';
        $serverNow = Carbon::now(self::ATTENDANCE_TIMEZONE);

        return view('front.pages.branch-portal.attendance.mark', [
            'title' => $mode === 'punch_out' ? 'Punch Out' : 'Punch In',
            'branch' => $request->attributes->get('branch_portal'),
            'roster' => $rosterRow,
            'attendance' => $attendance,
            'mode' => $mode,
            'scheduled_time' => $this->displayTimeRange($rosterRow->in_time, $rosterRow->out_time),
            'is_mobile_device' => $this->isMobileRequest($request),
            'submit_url' => $mode === 'punch_out'
                ? route('branch.portal.attendance.punch-out', ['roster' => $rosterRow->id])
                : route('branch.portal.attendance.punch-in', ['roster' => $rosterRow->id]),
            'server_now_seconds_of_day' => (int) $serverNow->format('H') * 3600
                + (int) $serverNow->format('i') * 60
                + (int) $serverNow->format('s'),
        ]);
    }

    public function punchIn(Request $request, int $roster)
    {
        return $this->storePunch($request, $roster, 'punch_in');
    }

    public function punchOut(Request $request, int $roster)
    {
        return $this->storePunch($request, $roster, 'punch_out');
    }

    private function storePunch(Request $request, int $rosterId, string $mode)
    {
        if (! $this->isMobileRequest($request)) {
            return redirect()
                ->route('branch.portal.attendance.index')
                ->with('error_message', 'Attendance can only be marked from a mobile device.');
        }

        $roster = $this->accessibleRoster($request, $rosterId);
        if (! $roster) {
            return redirect()
                ->route('branch.portal.attendance.index')
                ->with('error_message', 'This roster assignment is not available for attendance at this branch today.');
        }

        $validated = $request->validate([
            'photo' => 'required|string|max:5000000',
        ], [
            'photo.required' => 'Please capture a photo before confirming attendance.',
            'photo.max' => 'The captured photo is too large. Please capture it again.',
        ]);

        $photoBytes = $this->decodePhoto((string) $validated['photo']);
        $portalBranch = $request->attributes->get('branch_portal');
        $storedImagePath = null;

        try {
            $result = DB::transaction(function () use (
                $request,
                $roster,
                $portalBranch,
                $photoBytes,
                $mode,
                &$storedImagePath
            ) {
                if ($mode === 'punch_in') {
                    EmployeeAttendance::firstOrCreate(
                        ['roster_id' => (int) $roster->id],
                        $this->attendanceSnapshot($roster)
                    );
                }

                $attendance = EmployeeAttendance::where('roster_id', '=', $roster->id)
                    ->lockForUpdate()
                    ->first();

                if ($mode === 'punch_out' && (! $attendance || ! $attendance->punch_in_at)) {
                    return [
                        'saved' => false,
                        'message' => 'Punch In must be completed before Punch Out.',
                    ];
                }

                if ($mode === 'punch_in' && $attendance->punch_in_at) {
                    return [
                        'saved' => false,
                        'message' => $roster->employee_name.' has already punched in for this class.',
                    ];
                }

                if ($mode === 'punch_out' && $attendance->punch_out_at) {
                    return [
                        'saved' => false,
                        'message' => $roster->employee_name.' has already punched out for this class.',
                    ];
                }

                $punchedAt = Carbon::now(self::ATTENDANCE_TIMEZONE);
                $storedImagePath = $this->storePhoto($photoBytes, $roster, $mode, $punchedAt);

                if ($mode === 'punch_in') {
                    $lateMinutes = $this->lateMinutes($roster, $punchedAt);
                    $attendance->fill([
                        'is_late' => $lateMinutes > 0,
                        'late_minutes' => $lateMinutes,
                        'punch_in_at' => $punchedAt,
                        'punch_in_image' => $storedImagePath,
                        'punch_in_ip' => (string) $request->ip(),
                        'punch_in_portal_branch_id' => (int) $portalBranch->id,
                    ]);
                } else {
                    $attendance->fill([
                        'punch_out_at' => $punchedAt,
                        'punch_out_image' => $storedImagePath,
                        'punch_out_ip' => (string) $request->ip(),
                        'punch_out_portal_branch_id' => (int) $portalBranch->id,
                    ]);
                }

                $attendance->save();

                return [
                    'saved' => true,
                    'time' => $punchedAt->format('h:i A'),
                ];
            });
        } catch (\Throwable $e) {
            if ($storedImagePath) {
                File::delete(public_path(ltrim($storedImagePath, '/\\')));
            }

            report($e);

            return redirect()
                ->back()
                ->withInput()
                ->with('error_message', 'Attendance could not be saved. Please try again.');
        }

        if (! $result['saved']) {
            return redirect()
                ->route('branch.portal.attendance.mark', ['roster' => $roster->id])
                ->with('error_message', $result['message']);
        }

        $actionLabel = $mode === 'punch_in' ? 'punched in' : 'punched out';

        return redirect()
            ->route('branch.portal.attendance.index')
            ->with('success_message', $roster->employee_name.' '.$actionLabel.' at '.$result['time'].'.');
    }

    private function accessibleRoster(Request $request, int $rosterId)
    {
        $branch = $request->attributes->get('branch_portal');

        return EmployeeScheduleRoster::where('id', '=', $rosterId)
            ->whereIn('branch_id', $this->centreBranchIds($branch))
            ->whereIn('category', self::ROSTER_CATEGORIES)
            ->whereDate('roster_date', '=', Carbon::today(self::ATTENDANCE_TIMEZONE)->toDateString())
            ->where('status', '!=', 3)
            ->first();
    }

    private function centreBranchIds($branch): array
    {
        return Branch::whereRaw('LOWER(name) = ?', [Str::lower(trim((string) $branch->name))])
            ->where('status', '=', 1)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->map(function ($branchId) {
                return (int) $branchId;
            })
            ->values()
            ->all();
    }

    private function attendanceSnapshot($roster): array
    {
        return [
            'employee_id' => (int) $roster->employee_id,
            'employee_no' => (string) $roster->employee_no,
            'employee_name' => (string) $roster->employee_name,
            'category' => (string) $roster->category,
            'unit_id' => $roster->unit_id !== null ? (int) $roster->unit_id : null,
            'branch_id' => (int) $roster->branch_id,
            'branch_name' => (string) $roster->branch_name,
            'attendance_date' => Carbon::parse($roster->roster_date)->toDateString(),
            'scheduled_in_time' => (string) $roster->in_time,
            'scheduled_out_time' => (string) $roster->out_time,
        ];
    }

    private function attendanceState($attendance): string
    {
        if ($attendance && $attendance->punch_out_at) {
            return 'completed';
        }

        if ($attendance && $attendance->punch_in_at) {
            return 'punched_in';
        }

        return 'pending';
    }

    private function stateLabel(string $state): string
    {
        if ($state === 'completed') {
            return 'Completed';
        }

        if ($state === 'punched_in') {
            return 'Punch Out Pending';
        }

        return 'Punch In Pending';
    }

    private function displayTimeRange($inTime, $outTime): string
    {
        $formatTime = function ($time) {
            $time = trim((string) $time);
            if ($time === '') {
                return '';
            }

            try {
                return Carbon::createFromFormat('H:i', substr($time, 0, 5))->format('g:i A');
            } catch (\Throwable $e) {
                return $time;
            }
        };

        $formattedIn = $formatTime($inTime);
        $formattedOut = $formatTime($outTime);

        return trim($formattedIn.($formattedIn && $formattedOut ? ' - ' : '').$formattedOut);
    }

    private function lateMinutes($roster, Carbon $punchedAt): int
    {
        $scheduledInTime = substr(trim((string) $roster->in_time), 0, 5);
        if ($scheduledInTime === '') {
            return 0;
        }

        try {
            $scheduledAt = Carbon::createFromFormat(
                'Y-m-d H:i',
                Carbon::parse($roster->roster_date)->toDateString().' '.$scheduledInTime,
                self::ATTENDANCE_TIMEZONE
            );

            if (! $punchedAt->greaterThan($scheduledAt)) {
                return 0;
            }

            return (int) max(1, ceil($scheduledAt->diffInSeconds($punchedAt) / 60));
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function decodePhoto(string $photo): string
    {
        if (! preg_match('/^data:image\/jpeg;base64,(.+)$/s', $photo, $matches)) {
            throw ValidationException::withMessages([
                'photo' => 'The captured photo is invalid. Please capture it again.',
            ]);
        }

        $photoBytes = base64_decode(str_replace(' ', '+', $matches[1]), true);
        if ($photoBytes === false || strlen($photoBytes) < 1000 || strlen($photoBytes) > (3 * 1024 * 1024)) {
            throw ValidationException::withMessages([
                'photo' => 'The captured photo is invalid or too large. Please capture it again.',
            ]);
        }

        $imageInfo = @getimagesizefromstring($photoBytes);
        if (
            ! $imageInfo
            || ($imageInfo['mime'] ?? '') !== 'image/jpeg'
            || (int) ($imageInfo[0] ?? 0) < 200
            || (int) ($imageInfo[1] ?? 0) < 200
        ) {
            throw ValidationException::withMessages([
                'photo' => 'Please capture a clear camera photo before confirming attendance.',
            ]);
        }

        return $photoBytes;
    }

    private function storePhoto(string $photoBytes, $roster, string $mode, Carbon $punchedAt): string
    {
        $relativeDirectory = 'uploads/employee-attendance/'.$punchedAt->format('Y/m/d');
        $uploadDirectory = public_path($relativeDirectory);

        if (! File::exists($uploadDirectory)) {
            File::makeDirectory($uploadDirectory, 0755, true, true);
        }

        $filename = sprintf(
            '%s-roster-%d-%s-%s.jpg',
            str_replace('_', '-', $mode),
            (int) $roster->id,
            $punchedAt->format('His'),
            Str::uuid()->toString()
        );

        $absolutePath = $uploadDirectory.DIRECTORY_SEPARATOR.$filename;
        if (File::put($absolutePath, $photoBytes) === false) {
            throw new \RuntimeException('The attendance photo could not be stored.');
        }

        return '/'.str_replace('\\', '/', $relativeDirectory.'/'.$filename);
    }

    private function isMobileRequest(Request $request): bool
    {
        $userAgent = Str::lower((string) $request->userAgent());

        return (bool) preg_match(
            '/android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini|mobile|tablet/',
            $userAgent
        );
    }
}
