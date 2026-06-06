<?php

namespace App\Services;

use App\Models\EmployeeAttendance;
use App\Models\EmployeeScheduleRoster;
use Carbon\Carbon;

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

        $markedCount = 0;

        $query->chunkById(200, function ($rosters) use ($now, &$markedCount) {
            $attendances = EmployeeAttendance::whereIn('roster_id', $rosters->pluck('id'))
                ->get()
                ->keyBy('roster_id');

            foreach ($rosters as $roster) {
                if (! $this->classHasEnded($roster, $now)) {
                    continue;
                }

                $attendance = $attendances->get((int) $roster->id);
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
