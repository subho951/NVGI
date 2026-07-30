<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeScheduleRoster;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class EmployeeAttendanceAbsenceService
{
    private const ATTENDANCE_TIMEZONE = 'Asia/Kolkata';

    private const ROSTER_CATEGORIES = [
        'VHS TEACHER',
        'TSA TEACHER',
        'FRONT-DESK',
        'GROUP-D',
    ];

    public function sync(?Carbon $fromDate = null, ?Carbon $toDate = null, array $branchIds = []): int
    {
        $now = Carbon::now(self::ATTENDANCE_TIMEZONE);
        $query = EmployeeScheduleRoster::query()
            ->whereIn('category', self::ROSTER_CATEGORIES)
            ->where('status', '!=', 3)
            ->whereDate('roster_date', '<=', $now->toDateString())
            ->orderBy('id');

        if ($fromDate && $toDate) {
            $query->whereBetween('roster_date', [
                $fromDate->toDateString(),
                $toDate->toDateString(),
            ]);
        }

        if (! empty($branchIds)) {
            $query->whereIn('branch_id', $branchIds);
        }

        $holidayFrom = $fromDate
            ? $fromDate->copy()->startOfDay()
            : Carbon::create(2000, 1, 1, 0, 0, 0, self::ATTENDANCE_TIMEZONE);
        $holidayTo = $toDate ? $toDate->copy()->endOfDay() : $now->copy()->endOfDay();
        $holidayService = app(EmployeeHolidayService::class);
        $holidays = $holidayService->forPeriod($holidayFrom, $holidayTo);
        $markedCount = 0;

        $query->chunkById(200, function ($rosters) use (
            $now,
            $holidayService,
            $holidays,
            &$markedCount
        ) {
            $attendances = EmployeeAttendance::whereIn('roster_id', $rosters->pluck('id'))
                ->get()
                ->keyBy('roster_id');
            $employeeColumns = ['id'];

            if (Schema::hasColumn('employees', 'doj')) {
                $employeeColumns[] = 'doj';
            }

            $employees = Employee::whereIn('id', $rosters->pluck('employee_id')->unique())
                ->get($employeeColumns)
                ->keyBy('id');

            foreach ($rosters as $roster) {
                $attendance = $attendances->get((int) $roster->id);
                $employee = $employees->get((int) $roster->employee_id);

                if (
                    $this->isBeforeDateOfJoining($roster, $employee)
                    || $holidayService->applies(
                        $roster->roster_date,
                        (string) $roster->branch_name,
                        (string) $roster->category,
                        $holidays
                    )
                ) {
                    if ($attendance && $attendance->is_absent && ! $attendance->punch_in_at) {
                        $attendance->update([
                            'is_absent' => false,
                            'absent_marked_at' => null,
                        ]);
                    }

                    continue;
                }

                if (! $this->classHasEnded($roster, $now)) {
                    continue;
                }

                if ($attendance && $attendance->punch_in_at) {
                    if ($attendance->is_absent) {
                        $attendance->update([
                            'is_absent' => false,
                            'absent_marked_at' => null,
                        ]);
                    }

                    continue;
                }

                if ($attendance && $attendance->is_absent) {
                    continue;
                }

                EmployeeAttendance::updateOrCreate(
                    ['roster_id' => (int) $roster->id],
                    array_merge($this->attendanceSnapshot($roster), [
                        'is_late' => false,
                        'late_minutes' => 0,
                        'is_absent' => true,
                        'absent_marked_at' => $now,
                    ])
                );

                $markedCount++;
            }
        });

        return $markedCount;
    }

    private function isBeforeDateOfJoining($roster, $employee): bool
    {
        if (! $employee || empty($employee->doj)) {
            return false;
        }

        try {
            return Carbon::parse($roster->roster_date)->startOfDay()
                ->lessThan(Carbon::parse($employee->doj)->startOfDay());
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function classHasEnded($roster, Carbon $now): bool
    {
        try {
            $rosterDate = Carbon::parse($roster->roster_date, self::ATTENDANCE_TIMEZONE);
            $outTime = substr(trim((string) $roster->out_time), 0, 5);

            if ($outTime === '') {
                return $rosterDate->endOfDay()->lessThanOrEqualTo($now);
            }

            $scheduledEnd = Carbon::createFromFormat(
                'Y-m-d H:i',
                $rosterDate->toDateString().' '.$outTime,
                self::ATTENDANCE_TIMEZONE
            );
            $inTime = substr(trim((string) $roster->in_time), 0, 5);
            if ($inTime !== '' && $outTime <= $inTime) {
                $scheduledEnd->addDay();
            }

            return $scheduledEnd->lessThanOrEqualTo($now);
        } catch (\Throwable $e) {
            return false;
        }
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
}
