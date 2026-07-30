<?php

namespace Tests\Feature;

use App\Http\Controllers\SalaryGenerationController;
use Carbon\Carbon;
use ReflectionMethod;
use Tests\TestCase;

class SalaryGenerationCalculationTest extends TestCase
{
    public function test_mid_month_joiners_use_fixed_thirty_day_payroll_proration(): void
    {
        $period = [
            'start' => Carbon::create(2026, 7, 1)->startOfDay(),
            'end' => Carbon::create(2026, 7, 31)->startOfDay(),
            'days' => 31,
        ];

        $falguniPeriod = $this->invokeControllerMethod(
            'employeeSalaryPeriod',
            [$period, '2026-07-13']
        );
        $koyelPeriod = $this->invokeControllerMethod(
            'employeeSalaryPeriod',
            [$period, '2026-07-08']
        );

        $this->assertSame('2026-07-13', $falguniPeriod['start']->toDateString());
        $this->assertSame('2026-07-31', $falguniPeriod['end']->toDateString());
        $this->assertSame(30, $falguniPeriod['days']);
        $this->assertSame(18, $falguniPeriod['eligible_days']);
        $this->assertSame(6000.0, round(10000.0 * $falguniPeriod['eligible_days'] / $falguniPeriod['days'], 2));

        $this->assertSame('2026-07-08', $koyelPeriod['start']->toDateString());
        $this->assertSame(30, $koyelPeriod['days']);
        $this->assertSame(23, $koyelPeriod['eligible_days']);
        $this->assertSame(5366.67, round(7000.0 * $koyelPeriod['eligible_days'] / $koyelPeriod['days'], 2));
    }

    public function test_fixed_thirty_day_proration_is_independent_of_actual_month_length(): void
    {
        foreach ([
            ['start' => '2028-02-01', 'end' => '2028-02-29', 'doj' => '2028-02-13'],
            ['start' => '2026-04-01', 'end' => '2026-04-30', 'doj' => '2026-04-13'],
            ['start' => '2026-07-01', 'end' => '2026-07-31', 'doj' => '2026-07-13'],
        ] as $month) {
            $period = [
                'start' => Carbon::parse($month['start']),
                'end' => Carbon::parse($month['end']),
                'days' => Carbon::parse($month['start'])->daysInMonth,
            ];

            $employmentPeriod = $this->invokeControllerMethod(
                'employeeSalaryPeriod',
                [$period, $month['doj']]
            );

            $this->assertSame(30, $employmentPeriod['days']);
            $this->assertSame(18, $employmentPeriod['eligible_days']);
        }
    }

    public function test_full_month_employee_gets_thirty_days_and_joining_on_thirty_first_gets_one_day(): void
    {
        $period = [
            'start' => Carbon::create(2026, 7, 1)->startOfDay(),
            'end' => Carbon::create(2026, 7, 31)->startOfDay(),
            'days' => 31,
        ];

        $fullMonthPeriod = $this->invokeControllerMethod(
            'employeeSalaryPeriod',
            [$period, '2026-06-15']
        );
        $lastDayPeriod = $this->invokeControllerMethod(
            'employeeSalaryPeriod',
            [$period, '2026-07-31']
        );

        $this->assertSame(30, $fullMonthPeriod['eligible_days']);
        $this->assertSame(1, $lastDayPeriod['eligible_days']);
    }

    public function test_prorated_earning_components_reconcile_to_payable_gross(): void
    {
        $salaryHeads = collect([
            (object) ['id' => 1, 'type' => 'EARNING'],
            (object) ['id' => 2, 'type' => 'EARNING'],
            (object) ['id' => 3, 'type' => 'EARNING'],
            (object) ['id' => 4, 'type' => 'EARNING'],
        ]);
        $salaryValues = [
            1 => 2146.67,
            2 => 1073.33,
            3 => 1073.33,
            4 => 1073.33,
        ];
        $salaryHeadDetails = collect($salaryValues)
            ->map(fn ($amount, $salaryHeadId) => [
                'salary_head_id' => $salaryHeadId,
                'calculated_amount' => $amount,
            ])
            ->values()
            ->all();

        [$adjustedValues, $adjustedDetails] = $this->invokeControllerMethod(
            'reconcileProratedEarningRounding',
            [$salaryHeads, $salaryValues, $salaryHeadDetails, 5366.67]
        );

        $this->assertSame(5366.67, round(array_sum($adjustedValues), 2));
        $this->assertSame(1073.34, $adjustedValues[4]);
        $this->assertSame(1073.34, $adjustedDetails[3]['calculated_amount']);
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
            $this->assertSame(206.67, $result['amount']);
            $this->assertSame(4, $result['late_count']);
            $this->assertSame(1.0, $result['late_penalty_units']);
            $this->assertSame(206.67, $result['late_amount']);
            $this->assertSame(1, $result['holiday_days']);
            $this->assertSame(1, $result['pre_doj_excluded_days']);
            $this->assertSame(30, $result['details']['deduction_day_divisor']);
            $this->assertSame(206.67, $result['details']['deduction_day_rate']);
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
        $this->assertSame(413.33, $result['amount']);
    }

    public function test_tsa_ignores_late_and_deducts_short_hours_from_payable_gross(): void
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
                'eligible_days' => 30,
            ],
            6000.0,
        ]);

        $this->assertSame(0.0, $result['approved_leave_days']);
        $this->assertSame(0.0, $result['unpaid_absent_days']);
        $this->assertSame(10.0, $result['absent_hours']);
        $this->assertSame(12.0, $result['short_hours']);
        $this->assertSame(100.0, $result['hourly_rate']);
        $this->assertSame(1200.0, $result['amount']);
        $this->assertSame(0, $result['late_count']);
        $this->assertSame(0.0, $result['late_penalty_units']);
        $this->assertSame(0.0, $result['late_amount']);
        $this->assertSame([], $result['details']['late_dates']);
    }

    public function test_tsa_mid_month_hour_rate_uses_payable_not_full_month_gross(): void
    {
        $result = $this->invokeControllerMethod('absenceSnapshot', [
            'TSA TEACHER',
            10000.0,
            [],
            [
                'assigned_hours' => 60,
                'attendance_hours' => 54,
                'absent_hours' => 0,
                'late_count' => 3,
                'absent_dates' => [],
                'late_dates' => ['2026-07-20' => true],
            ],
            [
                'start' => Carbon::create(2026, 7, 13),
                'end' => Carbon::create(2026, 7, 31),
                'days' => 30,
                'eligible_days' => 18,
            ],
            6000.0,
        ]);

        $this->assertSame(6.0, $result['short_hours']);
        $this->assertSame(100.0, $result['hourly_rate']);
        $this->assertSame(600.0, $result['amount']);
        $this->assertSame(0, $result['late_count']);
        $this->assertSame(0.0, $result['late_amount']);
    }

    public function test_tsa_has_no_hour_deduction_when_assigned_hours_are_zero_or_attendance_is_not_short(): void
    {
        foreach ([
            ['assigned_hours' => 0, 'attendance_hours' => 0],
            ['assigned_hours' => 40, 'attendance_hours' => 40],
            ['assigned_hours' => 40, 'attendance_hours' => 42],
        ] as $attendance) {
            $result = $this->invokeControllerMethod('absenceSnapshot', [
                'TSA TEACHER',
                6000.0,
                [],
                $attendance + [
                    'absent_hours' => 0,
                    'late_count' => 0,
                    'absent_dates' => [],
                ],
                [
                    'start' => Carbon::create(2026, 7, 1),
                    'end' => Carbon::create(2026, 7, 31),
                    'days' => 30,
                    'eligible_days' => 30,
                ],
                6000.0,
            ]);

            $this->assertSame(0.0, $result['short_hours']);
            $this->assertSame(0.0, $result['amount']);
        }
    }

    public function test_non_tsa_categories_use_same_thirty_day_deduction_rate_for_every_month_length(): void
    {
        $months = [
            ['start' => '2026-02-01', 'end' => '2026-02-28', 'days' => 28],
            ['start' => '2026-04-01', 'end' => '2026-04-30', 'days' => 30],
            ['start' => '2026-07-01', 'end' => '2026-07-31', 'days' => 31],
        ];

        foreach ($months as $month) {
            $period = [
                'start' => Carbon::parse($month['start']),
                'end' => Carbon::parse($month['end']),
                'days' => $month['days'],
                'eligible_days' => 30,
            ];

            foreach (['VHS TEACHER', 'FRONT-DESK', 'GROUP-D'] as $category) {
                $result = $this->invokeControllerMethod('absenceSnapshot', [
                    $category,
                    30000.0,
                    [],
                    [
                        'assigned_hours' => 16,
                        'attendance_hours' => 8,
                        'absent_hours' => 8,
                        'late_count' => 3,
                        'absent_dates' => [$month['start'] => true],
                        'late_dates' => [$month['end'] => true],
                    ],
                    $period,
                ]);

                $this->assertSame(1000.0, $result['amount']);
                $this->assertSame(1000.0, $result['late_amount']);
                $this->assertSame(30, $result['details']['deduction_day_divisor']);
            }
        }
    }

    private function invokeControllerMethod(string $method, array $arguments)
    {
        $reflection = new ReflectionMethod(SalaryGenerationController::class, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs(app(SalaryGenerationController::class), $arguments);
    }
}
