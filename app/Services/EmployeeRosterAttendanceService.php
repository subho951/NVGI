<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeScheduleRoster;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmployeeRosterAttendanceService
{
    private const ATTENDANCE_TIMEZONE = 'Asia/Kolkata';

    public function markPresent(Carbon $attendanceDate, array $categories, int $userId = 0): array
    {
        $attendanceDate = $attendanceDate->copy()->timezone(self::ATTENDANCE_TIMEZONE)->startOfDay();
        $categories = collect($categories)
            ->map(fn ($category) => trim((string) $category))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $result = [
            'roster_assignments' => 0,
            'marked_present' => 0,
            'already_completed' => 0,
            'fallback_created' => 0,
            'fallback_restored' => 0,
            'skipped_employees' => 0,
            'skipped_invalid_time' => 0,
            'skipped_non_holidays' => 0,
            'skipped_pre_doj' => 0,
        ];

        if (empty($categories) || $attendanceDate->greaterThan(Carbon::today(self::ATTENDANCE_TIMEZONE))) {
            return $result;
        }

        return DB::transaction(function () use ($attendanceDate, $categories, $result) {
            [$selectedRosters, $eligibilitySkips] = $this->attendanceEligibleRosters(
                $this->selectedDateRosters($attendanceDate, $categories, true),
                $attendanceDate
            );
            $result['skipped_non_holidays'] = $eligibilitySkips['non_holidays'];
            $result['skipped_pre_doj'] = $eligibilitySkips['pre_doj'];
            $result['roster_assignments'] = $selectedRosters->count();

            foreach ($selectedRosters as $roster) {
                $punchInAt = $this->scheduledDateTime($attendanceDate, $roster->in_time);
                $punchOutAt = $this->scheduledDateTime($attendanceDate, $roster->out_time);

                if (! $punchInAt || ! $punchOutAt) {
                    $result['skipped_invalid_time']++;

                    continue;
                }

                if ($punchOutAt->lessThanOrEqualTo($punchInAt)) {
                    $punchOutAt->addDay();
                }

                $attendance = EmployeeAttendance::where('roster_id', '=', (int) $roster->id)
                    ->lockForUpdate()
                    ->first();
                $isNewAttendance = ! $attendance;

                if (! $attendance) {
                    $attendance = new EmployeeAttendance(
                        array_merge(
                            ['roster_id' => (int) $roster->id],
                            $this->attendanceSnapshot($roster)
                        )
                    );
                }

                $hadPunchIn = ! empty($attendance->punch_in_at);
                $hadPunchOut = ! empty($attendance->punch_out_at);
                $wasAbsent = (bool) $attendance->is_absent;

                if (! $hadPunchIn) {
                    $attendance->punch_in_at = $punchInAt;
                    $attendance->is_late = false;
                    $attendance->late_minutes = 0;
                }

                if (! $hadPunchOut) {
                    $attendance->punch_out_at = $punchOutAt;
                }

                $attendance->is_absent = false;
                $attendance->absent_marked_at = null;
                $attendance->save();

                if ($isNewAttendance || ! $hadPunchIn || ! $hadPunchOut || $wasAbsent) {
                    $result['marked_present']++;
                } else {
                    $result['already_completed']++;
                }
            }

            return $result;
        });
    }

    private function selectedDateRosters(Carbon $attendanceDate, array $categories, bool $lock = false)
    {
        $query = EmployeeScheduleRoster::whereIn('category', $categories)
            ->whereDate('roster_date', '=', $attendanceDate->toDateString())
            ->where('status', '!=', 3)
            ->orderBy('employee_id')
            ->orderBy('category')
            ->orderBy('branch_id')
            ->orderBy('in_time')
            ->orderBy('id');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->get();
    }

    private function attendanceEligibleRosters($rosters, Carbon $attendanceDate): array
    {
        $employeeColumns = ['id'];

        if (Schema::hasColumn('employees', 'doj')) {
            $employeeColumns[] = 'doj';
        }

        $employees = Employee::whereIn('id', $rosters->pluck('employee_id')->unique())
            ->get($employeeColumns)
            ->keyBy('id');
        $holidayService = app(EmployeeHolidayService::class);
        $holidays = $holidayService->forPeriod($attendanceDate, $attendanceDate);
        $skips = [
            'non_holidays' => 0,
            'pre_doj' => 0,
        ];

        $eligibleRosters = $rosters->filter(function ($roster) use (
            $attendanceDate,
            $employees,
            $holidayService,
            $holidays,
            &$skips
        ) {
            $employee = $employees->get((int) $roster->employee_id);

            if (
                $employee
                && ! empty($employee->doj)
                && $attendanceDate->lessThan(Carbon::parse($employee->doj)->startOfDay())
            ) {
                $skips['pre_doj']++;

                return false;
            }

            if (! $holidayService->applies(
                $attendanceDate,
                (string) $roster->branch_name,
                (string) $roster->category,
                $holidays
            )) {
                $skips['non_holidays']++;

                return false;
            }

            return true;
        })->values();

        return [$eligibleRosters, $skips];
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
            'scheduled_in_time' => substr(trim((string) $roster->in_time), 0, 5),
            'scheduled_out_time' => substr(trim((string) $roster->out_time), 0, 5),
        ];
    }

    private function scheduledDateTime(Carbon $attendanceDate, $time): ?Carbon
    {
        $time = substr(trim((string) $time), 0, 5);

        if ($time === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat(
                'Y-m-d H:i',
                $attendanceDate->toDateString().' '.$time,
                self::ATTENDANCE_TIMEZONE
            );
        } catch (\Throwable $e) {
            return null;
        }
    }

}
