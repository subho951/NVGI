<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeScheduleRoster;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        ];

        if (empty($categories)) {
            return $result;
        }

        return DB::transaction(function () use ($attendanceDate, $categories, $userId, $result) {
            $selectedRosters = $this->selectedDateRosters($attendanceDate, $categories);
            $selectedEmployeeCategories = $selectedRosters
                ->mapWithKeys(fn ($roster) => [$this->employeeCategoryKey($roster->employee_id, $roster->category) => true])
                ->all();

            $eligibleEmployeeCategories = $this->eligibleEmployeeCategories($categories);
            $previousRows = $this->latestPreviousRosterRows(
                $attendanceDate,
                $categories,
                array_keys($eligibleEmployeeCategories)
            );
            $previousEmployeeCategories = [];

            foreach ($previousRows as $previousRow) {
                $employeeCategoryKey = $this->employeeCategoryKey(
                    $previousRow->employee_id,
                    $previousRow->category
                );

                if (
                    isset($selectedEmployeeCategories[$employeeCategoryKey])
                    || ! isset($eligibleEmployeeCategories[$employeeCategoryKey])
                ) {
                    continue;
                }

                if (! $this->hasUsableTimes($previousRow)) {
                    $result['skipped_invalid_time']++;

                    continue;
                }

                $previousEmployeeCategories[$employeeCategoryKey] = true;
                $fallbackStatus = $this->copyRosterAssignment(
                    $previousRow,
                    $attendanceDate,
                    $userId
                );

                if ($fallbackStatus === 'created') {
                    $result['fallback_created']++;
                } elseif ($fallbackStatus === 'restored') {
                    $result['fallback_restored']++;
                }
            }

            $result['skipped_employees'] = collect($eligibleEmployeeCategories)
                ->keys()
                ->reject(fn ($key) => isset($selectedEmployeeCategories[$key]) || isset($previousEmployeeCategories[$key]))
                ->map(fn ($key) => (int) explode('|', $key, 2)[0])
                ->unique()
                ->count();

            $selectedRosters = $this->selectedDateRosters($attendanceDate, $categories, true);
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

    private function eligibleEmployeeCategories(array $categories): array
    {
        $eligible = [];

        Employee::where('status', '=', 1)
            ->get(['id', 'category'])
            ->each(function ($employee) use ($categories, &$eligible) {
                foreach ($this->employeeCategoryValues($employee->category) as $category) {
                    if (in_array($category, $categories, true)) {
                        $eligible[$this->employeeCategoryKey($employee->id, $category)] = (int) $employee->id;
                    }
                }
            });

        return $eligible;
    }

    private function latestPreviousRosterRows(
        Carbon $attendanceDate,
        array $categories,
        array $eligibleEmployeeCategoryKeys
    ) {
        $eligibleEmployeeIds = collect($eligibleEmployeeCategoryKeys)
            ->map(fn ($key) => (int) explode('|', $key, 2)[0])
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($eligibleEmployeeIds)) {
            return collect();
        }

        $latestDates = DB::table('employee_schedule_rosters as source')
            ->select([
                'source.employee_id',
                'source.category',
                DB::raw('MAX(source.roster_date) as latest_roster_date'),
            ])
            ->whereIn('source.employee_id', $eligibleEmployeeIds)
            ->whereIn('source.category', $categories)
            ->whereDate('source.roster_date', '<', $attendanceDate->toDateString())
            ->where('source.status', '!=', 3)
            ->groupBy('source.employee_id', 'source.category');

        return EmployeeScheduleRoster::from('employee_schedule_rosters as roster')
            ->joinSub($latestDates, 'latest', function ($join) {
                $join->on('latest.employee_id', '=', 'roster.employee_id')
                    ->on('latest.category', '=', 'roster.category')
                    ->on('latest.latest_roster_date', '=', 'roster.roster_date');
            })
            ->where('roster.status', '!=', 3)
            ->select('roster.*')
            ->orderBy('roster.employee_id')
            ->orderBy('roster.category')
            ->orderBy('roster.branch_id')
            ->orderBy('roster.in_time')
            ->orderBy('roster.id')
            ->get();
    }

    private function copyRosterAssignment(
        $sourceRoster,
        Carbon $attendanceDate,
        int $userId
    ): string {
        $keys = [
            'employee_id' => (int) $sourceRoster->employee_id,
            'category' => (string) $sourceRoster->category,
            'branch_id' => (int) $sourceRoster->branch_id,
            'roster_date' => $attendanceDate->toDateString(),
            'in_time' => substr(trim((string) $sourceRoster->in_time), 0, 5),
        ];
        $values = [
            'employee_no' => (string) $sourceRoster->employee_no,
            'employee_name' => (string) $sourceRoster->employee_name,
            'unit_id' => $sourceRoster->unit_id !== null ? (int) $sourceRoster->unit_id : null,
            'unit_name' => (string) $sourceRoster->unit_name,
            'branch_name' => (string) $sourceRoster->branch_name,
            'roster_month' => (int) $attendanceDate->month,
            'roster_year' => (int) $attendanceDate->year,
            'day_name' => $attendanceDate->format('l'),
            'out_time' => substr(trim((string) $sourceRoster->out_time), 0, 5),
            'status' => 1,
            'generated_at' => now(self::ATTENDANCE_TIMEZONE),
            'created_by' => $userId,
            'updated_by' => $userId,
        ];

        $roster = EmployeeScheduleRoster::where($keys)->first();

        if ($roster && (int) $roster->status !== 3) {
            return 'existing';
        }

        if ($roster) {
            $roster->fill($values);
            $roster->save();

            return 'restored';
        }

        EmployeeScheduleRoster::create(array_merge($keys, $values));

        return 'created';
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

    private function hasUsableTimes($roster): bool
    {
        return $this->scheduledDateTime(Carbon::today(self::ATTENDANCE_TIMEZONE), $roster->in_time)
            && $this->scheduledDateTime(Carbon::today(self::ATTENDANCE_TIMEZONE), $roster->out_time);
    }

    private function employeeCategoryValues($category): array
    {
        $decodedCategories = json_decode((string) $category, true);
        $categories = is_array($decodedCategories) ? $decodedCategories : [$category];

        return collect($categories)
            ->map(fn ($categoryValue) => trim((string) $categoryValue))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function employeeCategoryKey($employeeId, $category): string
    {
        return (int) $employeeId.'|'.trim((string) $category);
    }
}
