<?php

namespace App\Services;

use App\Models\EmployeeHoliday;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class EmployeeHolidayService
{
    public function forPeriod(Carbon $fromDate, Carbon $toDate): Collection
    {
        if (! Schema::hasTable('employee_holidays')) {
            return collect();
        }

        return EmployeeHoliday::where('status', '=', 1)
            ->whereBetween('holiday_date', [
                $fromDate->toDateString(),
                $toDate->toDateString(),
            ])
            ->orderBy('holiday_date')
            ->orderBy('id')
            ->get();
    }

    public function applies(
        $date,
        string $branchName,
        string $category,
        ?Collection $holidays = null
    ): bool {
        $date = Carbon::parse($date)->toDateString();
        $branchName = trim($branchName);
        $category = trim($category);
        $holidays = $holidays ?: $this->forPeriod(
            Carbon::parse($date),
            Carbon::parse($date)
        );

        return $holidays->contains(function ($holiday) use ($date, $branchName, $category) {
            if (Carbon::parse($holiday->holiday_date)->toDateString() !== $date) {
                return false;
            }

            $holidayBranch = trim((string) $holiday->branch_name);
            $holidayCategory = trim((string) $holiday->category);

            return ($holidayBranch === '' || strcasecmp($holidayBranch, $branchName) === 0)
                && ($holidayCategory === '' || strcasecmp($holidayCategory, $category) === 0);
        });
    }
}
