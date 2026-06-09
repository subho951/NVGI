<?php

namespace Tests\Feature;

use App\Http\Controllers\BranchPortalController;
use App\Http\Controllers\EmployeeScheduleRosterController;
use App\Models\EmployeeScheduleRoster;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TsaMultipleClassTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('units', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->text('name')->nullable();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('unit_id');
            $table->text('name')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->text('employee_no')->nullable();
            $table->text('first_name')->nullable();
            $table->text('middle_name')->nullable();
            $table->text('last_name')->nullable();
            $table->text('branch')->nullable();
            $table->text('category')->nullable();
            $table->tinyInteger('status')->default(1);
        });

        Schema::create('employee_schedule_rosters', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id')->index();
            $table->text('employee_no')->nullable();
            $table->text('employee_name')->nullable();
            $table->string('category', 100);
            $table->integer('unit_id')->nullable();
            $table->text('unit_name')->nullable();
            $table->integer('branch_id');
            $table->text('branch_name')->nullable();
            $table->date('roster_date');
            $table->unsignedTinyInteger('roster_month');
            $table->unsignedSmallInteger('roster_year');
            $table->string('day_name', 20)->nullable();
            $table->string('in_time', 5)->nullable();
            $table->string('out_time', 5)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('generated_at')->nullable();
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->timestamps();
            $table->unique(
                ['employee_id', 'category', 'branch_id', 'roster_date', 'in_time'],
                'employee_schedule_roster_time_unique'
            );
        });

        DB::table('units')->insert(['id' => 2, 'name' => 'TSA']);
        DB::table('branches')->insert([
            [
                'id' => 1,
                'unit_id' => 2,
                'name' => 'Bibirhat',
                'status' => 1,
            ],
            [
                'id' => 2,
                'unit_id' => 2,
                'name' => 'Mukundapur',
                'status' => 1,
            ],
        ]);
        DB::table('employees')->insert([
            'id' => 2,
            'employee_no' => 'NVGI-0002',
            'first_name' => 'MONISHA',
            'last_name' => 'DAS',
            'branch' => json_encode([1]),
            'category' => json_encode(['TSA TEACHER']),
            'status' => 1,
        ]);
        DB::table('employees')->insert([
            'id' => 3,
            'employee_no' => 'NVGI-0003',
            'first_name' => 'FRONT',
            'last_name' => 'DESK',
            'branch' => json_encode([1]),
            'category' => json_encode(['FRONT-DESK']),
            'status' => 1,
        ]);
        DB::table('employees')->insert([
            'id' => 4,
            'employee_no' => 'NVGI-0004',
            'first_name' => 'GROUP',
            'last_name' => 'D',
            'branch' => json_encode([1]),
            'category' => json_encode(['GROUP-D']),
            'status' => 1,
        ]);
        DB::table('employees')->insert([
            'id' => 5,
            'employee_no' => 'NVGI-0005',
            'first_name' => 'VHS',
            'last_name' => 'TEACHER',
            'branch' => json_encode([1, 2]),
            'category' => json_encode(['VHS TEACHER']),
            'status' => 1,
        ]);
        DB::table('employee_schedule_rosters')->insert($this->rosterRow('10:00', '11:30'));
    }

    public function test_non_overlapping_same_day_class_is_allowed_and_overlap_is_rejected(): void
    {
        $controller = app(EmployeeScheduleRosterController::class);

        $controller->tsaAddClass($this->requestFor('14:00', '16:00'));

        $this->assertDatabaseHas('employee_schedule_rosters', [
            'employee_id' => 2,
            'branch_id' => 1,
            'roster_date' => now()->toDateString(),
            'in_time' => '14:00',
            'out_time' => '16:00',
        ]);

        $controller->tsaAddClass($this->requestFor('11:00', '15:00'));

        $this->assertSame(2, EmployeeScheduleRoster::query()->count());
        $this->assertSame(
            'This TSA teacher already has another class overlapping the selected time.',
            session('error_message')
        );
    }

    public function test_tsa_additional_class_can_be_added_on_sunday(): void
    {
        $controller = app(EmployeeScheduleRosterController::class);
        $sunday = Carbon::create(2026, 6, 7);

        $controller->tsaAddClass($this->requestForDate($sunday, '14:00', '16:00'));

        $this->assertDatabaseHas('employee_schedule_rosters', [
            'employee_id' => 2,
            'branch_id' => 1,
            'roster_date' => '2026-06-07',
            'in_time' => '14:00',
            'out_time' => '16:00',
        ]);
    }

    public function test_front_desk_roster_can_save_optional_sunday_only(): void
    {
        $controller = app(EmployeeScheduleRosterController::class);
        $request = Request::create('/employee/schedule-roster/front-desk-group-d', 'POST', [
            'category' => 'FRONT-DESK',
            'month' => '2026-06',
            'employee_id' => 3,
            'branch_name' => 'Bibirhat',
            'times' => [
                '2026-06-07' => [
                    'in_time' => '08:30',
                    'out_time' => '20:00',
                ],
            ],
        ]);
        $request->setLaravelSession(app('session')->driver());

        $controller->support($request);

        $this->assertDatabaseHas('employee_schedule_rosters', [
            'employee_id' => 3,
            'category' => 'FRONT-DESK',
            'roster_date' => '2026-06-07',
            'in_time' => '08:30',
            'out_time' => '20:00',
        ]);
        $this->assertSame(1, EmployeeScheduleRoster::where('employee_id', 3)->count());
    }

    public function test_front_desk_weekoff_can_be_shifted_with_duty_date(): void
    {
        DB::table('employee_schedule_rosters')->insert($this->datedRosterRow(
            3,
            'NVGI-0003',
            'FRONT DESK',
            'FRONT-DESK',
            Carbon::create(2026, 6, 13),
            '08:30',
            '20:00'
        ));

        $controller = app(EmployeeScheduleRosterController::class);
        $request = Request::create('/employee/schedule-roster/front-desk-group-d/shift-date', 'POST', [
            'category' => 'FRONT-DESK',
            'employee_id' => 3,
            'branch_name' => 'Bibirhat',
            'source_date' => '2026-06-10',
            'target_date' => '2026-06-13',
        ]);
        $request->setLaravelSession(app('session')->driver());

        $controller->supportShiftDate($request);

        $this->assertDatabaseHas('employee_schedule_rosters', [
            'employee_id' => 3,
            'category' => 'FRONT-DESK',
            'roster_date' => '2026-06-10',
            'in_time' => '08:30',
            'out_time' => '20:00',
            'status' => 1,
        ]);
        $this->assertDatabaseMissing('employee_schedule_rosters', [
            'employee_id' => 3,
            'category' => 'FRONT-DESK',
            'roster_date' => '2026-06-13',
            'status' => 1,
        ]);
    }

    public function test_tsa_weekoff_can_be_shifted_with_duty_date(): void
    {
        DB::table('employee_schedule_rosters')->insert($this->datedRosterRow(
            2,
            'NVGI-0002',
            'MONISHA DAS',
            'TSA TEACHER',
            Carbon::create(2026, 6, 13),
            '14:00',
            '16:00'
        ));

        $controller = app(EmployeeScheduleRosterController::class);
        $request = Request::create('/employee/schedule-roster/tsa/shift-date', 'POST', [
            'category' => 'TSA TEACHER',
            'employee_id' => 2,
            'branch_name' => '',
            'source_date' => '2026-06-10',
            'target_date' => '2026-06-13',
        ]);
        $request->setLaravelSession(app('session')->driver());

        $controller->tsaShiftDate($request);

        $this->assertDatabaseHas('employee_schedule_rosters', [
            'employee_id' => 2,
            'category' => 'TSA TEACHER',
            'roster_date' => '2026-06-10',
            'in_time' => '14:00',
            'out_time' => '16:00',
            'status' => 1,
        ]);
        $this->assertDatabaseMissing('employee_schedule_rosters', [
            'employee_id' => 2,
            'category' => 'TSA TEACHER',
            'roster_date' => '2026-06-13',
            'status' => 1,
        ]);
    }

    public function test_group_d_individual_roster_date_can_be_deleted(): void
    {
        DB::table('employee_schedule_rosters')->insert($this->datedRosterRow(
            4,
            'NVGI-0004',
            'GROUP D',
            'GROUP-D',
            Carbon::create(2026, 6, 18),
            '08:30',
            '20:00'
        ));
        $rosterId = (int) EmployeeScheduleRoster::where('employee_id', 4)->value('id');

        $controller = app(EmployeeScheduleRosterController::class);
        $request = Request::create('/employee/schedule-roster/front-desk-group-d/delete-date', 'POST', [
            'roster_id' => $rosterId,
        ]);
        $request->setLaravelSession(app('session')->driver());

        $controller->supportDeleteDate($request);

        $this->assertDatabaseHas('employee_schedule_rosters', [
            'id' => $rosterId,
            'employee_id' => 4,
            'category' => 'GROUP-D',
            'roster_date' => '2026-06-18',
            'status' => 3,
        ]);
    }

    public function test_tsa_individual_roster_date_can_be_deleted(): void
    {
        $rosterId = (int) EmployeeScheduleRoster::where('employee_id', 2)->value('id');

        $controller = app(EmployeeScheduleRosterController::class);
        $request = Request::create('/employee/schedule-roster/tsa/delete-date', 'POST', [
            'roster_id' => $rosterId,
        ]);
        $request->setLaravelSession(app('session')->driver());

        $controller->tsaDeleteDate($request);

        $this->assertDatabaseHas('employee_schedule_rosters', [
            'id' => $rosterId,
            'employee_id' => 2,
            'category' => 'TSA TEACHER',
            'status' => 3,
        ]);
    }

    public function test_vhs_teacher_roster_can_be_deleted_for_selected_branch(): void
    {
        DB::table('employee_schedule_rosters')->insert([
            $this->datedRosterRow(
                5,
                'NVGI-0005',
                'VHS TEACHER',
                'VHS TEACHER',
                Carbon::create(2026, 6, 1),
                '10:00',
                '15:00',
                1,
                'Bibirhat'
            ),
            $this->datedRosterRow(
                5,
                'NVGI-0005',
                'VHS TEACHER',
                'VHS TEACHER',
                Carbon::create(2026, 6, 1),
                '10:00',
                '15:00',
                2,
                'Mukundapur'
            ),
        ]);

        $controller = app(EmployeeScheduleRosterController::class);
        $request = Request::create('/employee/schedule-roster/vhs/delete', 'POST', [
            'delete_month' => '2026-06',
            'employee_id' => 5,
            'branch_id' => 1,
        ]);
        $request->setLaravelSession(app('session')->driver());

        $controller->vhsDelete($request);

        $this->assertDatabaseHas('employee_schedule_rosters', [
            'employee_id' => 5,
            'category' => 'VHS TEACHER',
            'branch_id' => 1,
            'status' => 3,
        ]);
        $this->assertDatabaseHas('employee_schedule_rosters', [
            'employee_id' => 5,
            'category' => 'VHS TEACHER',
            'branch_id' => 2,
            'status' => 1,
        ]);
    }

    public function test_vhs_individual_roster_can_be_created_for_selected_employee(): void
    {
        $controller = app(EmployeeScheduleRosterController::class);
        $request = Request::create('/employee/schedule-roster/vhs/individual', 'POST', [
            'category' => 'VHS TEACHER',
            'month' => '2026-06',
            'employee_id' => 5,
            'branch_name' => 'Bibirhat',
            'times' => [
                '2026-06-01' => [
                    'in_time' => '10:00',
                    'out_time' => '15:00',
                ],
                '2026-06-06' => [
                    'in_time' => '09:30',
                    'out_time' => '14:30',
                ],
            ],
        ]);
        $request->setLaravelSession(app('session')->driver());

        $controller->vhsIndividual($request);

        $this->assertDatabaseHas('employee_schedule_rosters', [
            'employee_id' => 5,
            'category' => 'VHS TEACHER',
            'branch_id' => 1,
            'roster_date' => '2026-06-01',
            'in_time' => '10:00',
            'out_time' => '15:00',
            'status' => 1,
        ]);
        $this->assertDatabaseHas('employee_schedule_rosters', [
            'employee_id' => 5,
            'category' => 'VHS TEACHER',
            'branch_id' => 1,
            'roster_date' => '2026-06-06',
            'in_time' => '09:30',
            'out_time' => '14:30',
            'status' => 1,
        ]);
        $this->assertSame(2, EmployeeScheduleRoster::where('employee_id', 5)->count());
    }

    public function test_vhs_first_saturday_roster_time_can_be_updated(): void
    {
        DB::table('employee_schedule_rosters')->insert($this->datedRosterRow(
            5,
            'NVGI-0005',
            'VHS TEACHER',
            'VHS TEACHER',
            Carbon::create(2026, 6, 6),
            '10:00',
            '15:00'
        ));
        $rosterId = (int) EmployeeScheduleRoster::where('employee_id', 5)->value('id');

        $controller = app(EmployeeScheduleRosterController::class);
        $request = Request::create('/employee/schedule-roster/vhs/update-time', 'POST', [
            'roster_id' => $rosterId,
            'in_time' => '09:30',
            'out_time' => '14:30',
        ]);
        $request->setLaravelSession(app('session')->driver());

        $controller->vhsUpdateTime($request);

        $this->assertDatabaseHas('employee_schedule_rosters', [
            'id' => $rosterId,
            'category' => 'VHS TEACHER',
            'roster_date' => '2026-06-06',
            'in_time' => '09:30',
            'out_time' => '14:30',
        ]);
    }

    public function test_vhs_weekday_roster_time_cannot_be_updated(): void
    {
        DB::table('employee_schedule_rosters')->insert($this->datedRosterRow(
            5,
            'NVGI-0005',
            'VHS TEACHER',
            'VHS TEACHER',
            Carbon::create(2026, 6, 1),
            '10:00',
            '15:00'
        ));
        $rosterId = (int) EmployeeScheduleRoster::where('employee_id', 5)->value('id');

        $controller = app(EmployeeScheduleRosterController::class);
        $request = Request::create('/employee/schedule-roster/vhs/update-time', 'POST', [
            'roster_id' => $rosterId,
            'in_time' => '09:30',
            'out_time' => '14:30',
        ]);
        $request->setLaravelSession(app('session')->driver());

        $controller->vhsUpdateTime($request);

        $this->assertDatabaseHas('employee_schedule_rosters', [
            'id' => $rosterId,
            'category' => 'VHS TEACHER',
            'roster_date' => '2026-06-01',
            'in_time' => '10:00',
            'out_time' => '15:00',
        ]);
    }

    public function test_roster_branch_colors_are_fixed_by_branch_name(): void
    {
        $expected = [
            'Bibirhat' => 'violet',
            'Mukundapur' => 'yellow',
            'Rajarhat' => 'light-green',
        ];

        foreach ([
            app(EmployeeScheduleRosterController::class),
            app(BranchPortalController::class),
        ] as $controller) {
            $method = new \ReflectionMethod($controller, 'branchColorClass');
            $method->setAccessible(true);

            foreach ($expected as $branchName => $colorClass) {
                $this->assertSame($colorClass, $method->invoke($controller, $branchName));
            }
        }
    }

    public function test_roster_pdf_templates_render_branch_color_legend(): void
    {
        $legend = [
            ['class' => 'violet', 'label' => 'Bibirhat', 'color' => '#c7b7ff'],
            ['class' => 'yellow', 'label' => 'Mukundapur', 'color' => '#ffed9d'],
            ['class' => 'light-green', 'label' => 'Rajarhat', 'color' => '#b7f3c8'],
        ];
        $common = [
            'selectedMonthLabel' => 'June 2026',
            'monthStartDate' => Carbon::create(2026, 6, 1),
            'monthEndDate' => Carbon::create(2026, 6, 30),
            'calendarDates' => [],
            'branchColorLegend' => $legend,
        ];

        $vhsHtml = view('front.pages.employee.schedule-roster-vhs-pdf', array_merge($common, [
            'calendarEmployees' => [],
            'calendarCells' => [],
            'selectedBranchLabel' => '',
            'selectedEmployeeLabel' => '',
        ]))->render();

        $supportHtml = view('front.pages.employee.schedule-roster-support-pdf', array_merge($common, [
            'title' => 'Front Desk & Group D Schedule Roster',
            'calendarGroups' => [],
            'selectedCategory' => '',
            'selectedBranchName' => '',
            'selectedEmployeeLabel' => '',
        ]))->render();

        foreach ([$vhsHtml, $supportHtml] as $html) {
            $this->assertStringContainsString('Bibirhat', $html);
            $this->assertStringContainsString('Mukundapur', $html);
            $this->assertStringContainsString('Rajarhat', $html);
            $this->assertStringContainsString('#c7b7ff', $html);
            $this->assertStringContainsString('#ffed9d', $html);
            $this->assertStringContainsString('#b7f3c8', $html);
        }
    }

    private function requestFor(string $inTime, string $outTime): Request
    {
        $request = Request::create('/employee/schedule-roster/tsa/add-class', 'POST', [
            'month' => now()->format('Y-m'),
            'employee_id' => 2,
            'branch_name' => 'Bibirhat',
            'roster_date' => now()->toDateString(),
            'in_time' => $inTime,
            'out_time' => $outTime,
        ]);
        $request->setLaravelSession(app('session')->driver());

        return $request;
    }

    private function requestForDate(Carbon $rosterDate, string $inTime, string $outTime): Request
    {
        $request = Request::create('/employee/schedule-roster/tsa/add-class', 'POST', [
            'month' => $rosterDate->format('Y-m'),
            'employee_id' => 2,
            'branch_name' => 'Bibirhat',
            'roster_date' => $rosterDate->toDateString(),
            'in_time' => $inTime,
            'out_time' => $outTime,
        ]);
        $request->setLaravelSession(app('session')->driver());

        return $request;
    }

    private function rosterRow(string $inTime, string $outTime): array
    {
        return [
            'employee_id' => 2,
            'employee_no' => 'NVGI-0002',
            'employee_name' => 'MONISHA DAS',
            'category' => 'TSA TEACHER',
            'unit_id' => 2,
            'unit_name' => 'TSA',
            'branch_id' => 1,
            'branch_name' => 'Bibirhat',
            'roster_date' => now()->toDateString(),
            'roster_month' => now()->month,
            'roster_year' => now()->year,
            'day_name' => now()->format('l'),
            'in_time' => $inTime,
            'out_time' => $outTime,
            'status' => 1,
            'generated_at' => now(),
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function datedRosterRow(
        int $employeeId,
        string $employeeNo,
        string $employeeName,
        string $category,
        Carbon $rosterDate,
        string $inTime,
        string $outTime,
        int $branchId = 1,
        string $branchName = 'Bibirhat'
    ): array {
        return [
            'employee_id' => $employeeId,
            'employee_no' => $employeeNo,
            'employee_name' => $employeeName,
            'category' => $category,
            'unit_id' => 2,
            'unit_name' => 'TSA',
            'branch_id' => $branchId,
            'branch_name' => $branchName,
            'roster_date' => $rosterDate->toDateString(),
            'roster_month' => $rosterDate->month,
            'roster_year' => $rosterDate->year,
            'day_name' => $rosterDate->format('l'),
            'in_time' => $inTime,
            'out_time' => $outTime,
            'status' => 1,
            'generated_at' => now(),
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
