<?php

namespace Tests\Feature;

use App\Http\Controllers\SalaryGenerationController;
use Carbon\Carbon;
use ReflectionMethod;
use Tests\TestCase;

class SalaryGenerationCalculationTest extends TestCase
{
    public function test_employee_joining_on_ninth_is_eligible_from_ninth_through_month_end(): void
    {
        $period = [
            'start' => Carbon::create(2026, 7, 1)->startOfDay(),
            'end' => Carbon::create(2026, 7, 31)->startOfDay(),
            'days' => 31,
        ];

        $employmentPeriod = $this->invokeControllerMethod(
            'employeeSalaryPeriod',
            [$period, '2026-07-09']
        );

        $this->assertSame('2026-07-09', $employmentPeriod['start']->toDateString());
        $this->assertSame('2026-07-31', $employmentPeriod['end']->toDateString());
        $this->assertSame(23, $employmentPeriod['eligible_days']);
    }

    public function test_vhs_front_desk_and_group_d_deduct_only_uncovered_absence_and_late_separately(): void
    {
        $attendance = [
            'assigned_hours' => 40,
            'attendance_hours' => 24,
            'absent_hours' => 16,
            'late_count' => 4,
            'absent_dates' => [
                '2026-07-15' => true,
                '2026-07-17' => true,
            ],
            'late_dates' => [
                '2026-07-10' => true,
                '2026-07-22' => true,
                '2026-07-28' => true,
                '2026-07-29' => true,
            ],
            'holiday_dates' => ['2026-07-06' => true],
            'pre_doj_dates' => ['2026-07-01' => true],
        ];
        $period = [
            'start' => Carbon::create(2026, 7, 1),
            'end' => Carbon::create(2026, 7, 31),
            'days' => 31,
            'eligible_days' => 31,
        ];

        foreach (['VHS TEACHER', 'FRONT-DESK', 'GROUP-D'] as $category) {
            $result = $this->invokeControllerMethod('absenceSnapshot', [
                $category,
                6200.0,
                ['2026-07-15' => 1],
                $attendance,
                $period,
            ]);

            $this->assertSame(2.0, $result['absent_days']);
            $this->assertSame(1.0, $result['approved_leave_days']);
            $this->assertSame(1.0, $result['unpaid_absent_days']);
            $this->assertSame(200.0, $result['amount']);
            $this->assertSame(4, $result['late_count']);
            $this->assertSame(1.0, $result['late_penalty_units']);
            $this->assertSame(200.0, $result['late_amount']);
            $this->assertSame(1, $result['holiday_days']);
            $this->assertSame(1, $result['pre_doj_excluded_days']);
        }
    }

    public function test_unused_leave_balance_does_not_waive_an_absence(): void
    {
        $result = $this->invokeControllerMethod('absenceSnapshot', [
            'VHS TEACHER',
            6200.0,
            [],
            [
                'assigned_hours' => 16,
                'attendance_hours' => 0,
                'absent_hours' => 16,
                'late_count' => 0,
                'absent_dates' => [
                    '2026-07-15' => true,
                    '2026-07-16' => true,
                ],
            ],
            [
                'start' => Carbon::create(2026, 7, 1),
                'end' => Carbon::create(2026, 7, 31),
                'days' => 31,
                'eligible_days' => 31,
            ],
        ]);

        $this->assertSame(2.0, $result['unpaid_absent_days']);
        $this->assertSame(400.0, $result['amount']);
    }

    public function test_tsa_ignores_leave_and_deducts_absent_roster_hours_and_late_separately(): void
    {
        $result = $this->invokeControllerMethod('absenceSnapshot', [
            'TSA TEACHER',
            6000.0,
            ['2026-07-15' => 1],
            [
                'assigned_hours' => 60,
                'attendance_hours' => 48,
                'absent_hours' => 10,
                'late_count' => 4,
                'absent_dates' => [
                    '2026-07-15' => true,
                    '2026-07-17' => true,
                ],
                'late_dates' => ['2026-07-20' => true],
            ],
            [
                'start' => Carbon::create(2026, 7, 1),
                'end' => Carbon::create(2026, 7, 31),
                'days' => 31,
                'eligible_days' => 31,
            ],
        ]);

        $this->assertSame(0.0, $result['approved_leave_days']);
        $this->assertSame(10.0, $result['absent_hours']);
        $this->assertSame(1000.0, $result['amount']);
        $this->assertSame(1.0, $result['late_penalty_units']);
        $this->assertSame(100.0, $result['late_amount']);
    }

    private function invokeControllerMethod(string $method, array $arguments)
    {
        $reflection = new ReflectionMethod(SalaryGenerationController::class, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs(app(SalaryGenerationController::class), $arguments);
    }
}
