<?php

namespace Tests\Feature;

use App\Http\Controllers\EmployeeScheduleRosterController;
use App\Models\EmployeeAttendance;
use App\Services\EmployeeRosterAttendanceService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmployeeRosterAttendanceServiceTest extends TestCase
{
    private Carbon $selectedDate;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 7, 30, 18, 0, 0, 'Asia/Kolkata'));
        $this->selectedDate = Carbon::create(2026, 7, 29, 0, 0, 0, 'Asia/Kolkata');

        Schema::create('employees', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->text('employee_no')->nullable();
            $table->text('first_name')->nullable();
            $table->text('middle_name')->nullable();
            $table->text('last_name')->nullable();
            $table->text('category')->nullable();
            $table->date('doj')->nullable();
            $table->tinyInteger('status')->default(1);
        });

        Schema::create('employee_holidays', function (Blueprint $table) {
            $table->id();
            $table->date('holiday_date')->index();
            $table->string('name');
            $table->string('branch_name')->nullable();
            $table->string('category')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->timestamps();
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

        Schema::create('employee_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('roster_id')->unique();
            $table->integer('employee_id')->index();
            $table->text('employee_no')->nullable();
            $table->text('employee_name')->nullable();
            $table->string('category', 100)->index();
            $table->integer('unit_id')->nullable();
            $table->integer('branch_id')->index();
            $table->text('branch_name')->nullable();
            $table->date('attendance_date')->index();
            $table->string('scheduled_in_time', 5)->nullable();
            $table->string('scheduled_out_time', 5)->nullable();
            $table->boolean('is_late')->default(false);
            $table->unsignedSmallInteger('late_minutes')->default(0);
            $table->boolean('is_absent')->default(false);
            $table->timestamp('absent_marked_at')->nullable();
            $table->timestamp('punch_in_at')->nullable();
            $table->text('punch_in_image')->nullable();
            $table->string('punch_in_ip', 45)->nullable();
            $table->integer('punch_in_portal_branch_id')->nullable();
            $table->timestamp('punch_out_at')->nullable();
            $table->text('punch_out_image')->nullable();
            $table->string('punch_out_ip', 45)->nullable();
            $table->integer('punch_out_portal_branch_id')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_admin_bulk_attendance_routes_are_registered(): void
    {
        $routes = app('router')->getRoutes();
        $expectedRoutes = [
            'employee.schedule-roster.vhs.mark-attendance' => 'vhsMarkAttendance',
            'employee.schedule-roster.support.mark-attendance' => 'supportMarkAttendance',
            'employee.schedule-roster.tsa.mark-attendance' => 'tsaMarkAttendance',
        ];

        foreach ($expectedRoutes as $routeName => $controllerMethod) {
            $route = $routes->getByName($routeName);

            $this->assertNotNull($route);
            $this->assertContains('POST', $route->methods());
            $this->assertStringEndsWith('@'.$controllerMethod, $route->getActionName());
        }
    }

    public function test_each_roster_page_action_marks_only_its_categories_present(): void
    {
        $this->insertHoliday();
        $this->insertEmployee(1, 'VHS TEACHER');
        $this->insertEmployee(2, 'TSA TEACHER');
        $this->insertEmployee(3, 'FRONT-DESK');
        $this->insertEmployee(4, 'GROUP-D');

        $this->insertRoster(1, 'VHS TEACHER', $this->selectedDate, '10:00', '15:00');
        $this->insertRoster(2, 'TSA TEACHER', $this->selectedDate, '09:00', '11:00');
        $this->insertRoster(3, 'FRONT-DESK', $this->selectedDate, '08:30', '17:30');
        $this->insertRoster(4, 'GROUP-D', $this->selectedDate, '08:00', '20:00');

        $controller = app(EmployeeScheduleRosterController::class);
        $controller->vhsMarkAttendance($this->attendanceRequest(
            '/employee/schedule-roster/vhs/mark-attendance'
        ));

        $this->assertSame(1, EmployeeAttendance::where('category', 'VHS TEACHER')->count());
        $this->assertSame(0, EmployeeAttendance::where('category', 'TSA TEACHER')->count());
        $this->assertSame(0, EmployeeAttendance::whereIn('category', ['FRONT-DESK', 'GROUP-D'])->count());

        $controller->supportMarkAttendance($this->attendanceRequest(
            '/employee/schedule-roster/front-desk-group-d/mark-attendance'
        ));
        $controller->tsaMarkAttendance($this->attendanceRequest(
            '/employee/schedule-roster/tsa/mark-attendance'
        ));

        $this->assertSame(4, EmployeeAttendance::count());
        $this->assertDatabaseHas('employee_attendances', [
            'employee_id' => 1,
            'scheduled_in_time' => '10:00',
            'scheduled_out_time' => '15:00',
            'is_absent' => false,
        ]);

        $vhsAttendance = EmployeeAttendance::where('employee_id', 1)->firstOrFail();
        $this->assertSame('2026-07-29', $vhsAttendance->attendance_date->toDateString());
        $this->assertSame('2026-07-29 10:00', $vhsAttendance->punch_in_at->format('Y-m-d H:i'));
        $this->assertSame('2026-07-29 15:00', $vhsAttendance->punch_out_at->format('Y-m-d H:i'));
    }

    public function test_working_day_does_not_complete_manual_punch_or_create_attendance(): void
    {
        $this->insertEmployee(1, 'VHS TEACHER');
        $id = $this->insertRoster(1, 'VHS TEACHER', $this->selectedDate, '08:45', '15:30');
        EmployeeAttendance::create(array_merge(
            $this->attendanceSnapshot($id, 1, 'VHS TEACHER', '08:45', '15:30'),
            ['punch_in_at' => $this->selectedDate->copy()->setTime(8, 44), 'punch_in_image' => '/manual.jpg']
        ));
        $this->insertEmployee(2, 'VHS TEACHER');
        $this->insertRoster(2, 'VHS TEACHER', $this->selectedDate, '08:45', '15:30');

        $controller = app(EmployeeScheduleRosterController::class);
        $response = $controller->vhsMarkAttendance($this->attendanceRequest('/employee/schedule-roster/vhs/mark-attendance'));

        $this->assertTrue($response->getSession()->has('error_message'));
        $this->assertSame(1, EmployeeAttendance::count());
        $attendance = EmployeeAttendance::firstOrFail();
        $this->assertNull($attendance->punch_out_at);
        $this->assertSame('/manual.jpg', $attendance->punch_in_image);
        $this->assertSame('08:44', $attendance->punch_in_at->format('H:i'));
    }

    public function test_holiday_scope_excludes_other_branches_categories_and_deleted_rosters(): void
    {
        $this->insertHoliday('Bibirhat', 'VHS TEACHER');
        $this->insertRoster(1, 'VHS TEACHER', $this->selectedDate, '09:00', '15:00');
        $this->insertRoster(2, 'VHS TEACHER', $this->selectedDate, '09:00', '15:00', 2, 'Mukundapur');
        $this->insertRoster(3, 'TSA TEACHER', $this->selectedDate, '09:00', '15:00');
        $deleted = $this->insertRoster(4, 'VHS TEACHER', $this->selectedDate, '09:00', '15:00');
        DB::table('employee_schedule_rosters')->where('id', $deleted)->update(['status' => 3]);
        $service = app(EmployeeRosterAttendanceService::class);
        $result = $service->markPresent($this->selectedDate, ['VHS TEACHER', 'TSA TEACHER']);
        $this->assertSame(1, $result['marked_present']);
        $this->assertSame(2, $result['skipped_non_holidays']);
        $this->assertSame(1, EmployeeAttendance::firstOrFail()->employee_id);
        $result = $service->markPresent($this->selectedDate, ['VHS TEACHER', 'TSA TEACHER']);
        $this->assertSame(1, $result['already_completed']);
        $this->assertSame(1, EmployeeAttendance::count());
    }

    public function test_existing_real_punch_is_preserved_and_absent_attendance_is_converted(): void
    {
        $this->insertHoliday();
        $this->insertEmployee(3, 'FRONT-DESK');
        $this->insertEmployee(4, 'GROUP-D');
        $frontRosterId = $this->insertRoster(
            3,
            'FRONT-DESK',
            $this->selectedDate,
            '08:30',
            '17:30'
        );
        $groupRosterId = $this->insertRoster(
            4,
            'GROUP-D',
            $this->selectedDate,
            '08:00',
            '20:00'
        );

        EmployeeAttendance::create(array_merge(
            $this->attendanceSnapshot($frontRosterId, 3, 'FRONT-DESK', '08:30', '17:30'),
            [
                'is_late' => true,
                'late_minutes' => 7,
                'punch_in_at' => Carbon::create(2026, 7, 29, 8, 37, 0, 'Asia/Kolkata'),
            ]
        ));
        EmployeeAttendance::create(array_merge(
            $this->attendanceSnapshot($groupRosterId, 4, 'GROUP-D', '08:00', '20:00'),
            [
                'is_absent' => true,
                'absent_marked_at' => Carbon::create(2026, 7, 29, 20, 1, 0, 'Asia/Kolkata'),
            ]
        ));

        app(EmployeeRosterAttendanceService::class)->markPresent(
            $this->selectedDate,
            ['FRONT-DESK', 'GROUP-D']
        );

        $frontAttendance = EmployeeAttendance::where('roster_id', $frontRosterId)->firstOrFail();
        $this->assertSame('2026-07-29 08:37', $frontAttendance->punch_in_at->format('Y-m-d H:i'));
        $this->assertSame('2026-07-29 17:30', $frontAttendance->punch_out_at->format('Y-m-d H:i'));
        $this->assertTrue($frontAttendance->is_late);
        $this->assertSame(7, $frontAttendance->late_minutes);

        $groupAttendance = EmployeeAttendance::where('roster_id', $groupRosterId)->firstOrFail();
        $this->assertSame('2026-07-29 08:00', $groupAttendance->punch_in_at->format('Y-m-d H:i'));
        $this->assertSame('2026-07-29 20:00', $groupAttendance->punch_out_at->format('Y-m-d H:i'));
        $this->assertFalse($groupAttendance->is_absent);
        $this->assertNull($groupAttendance->absent_marked_at);
        $this->assertFalse($groupAttendance->is_late);
        $this->assertSame(0, $groupAttendance->late_minutes);
    }

    public function test_employee_without_a_selected_or_previous_roster_is_reported_and_not_fabricated(): void
    {
        $this->insertEmployee(5, 'VHS TEACHER');

        $result = app(EmployeeRosterAttendanceService::class)->markPresent(
            $this->selectedDate,
            ['VHS TEACHER']
        );

        $this->assertSame(0, $result['marked_present']);
        $this->assertSame(0, $result['roster_assignments']);
        $this->assertSame(0, DB::table('employee_schedule_rosters')->count());
        $this->assertSame(0, EmployeeAttendance::count());
    }

    public function test_bulk_attendance_skips_rosters_before_employee_joining_date(): void
    {
        $this->insertHoliday();
        $this->insertEmployee(6, 'VHS TEACHER', '2026-07-30');
        $this->insertRoster(6, 'VHS TEACHER', $this->selectedDate, '10:00', '15:00');

        $result = app(EmployeeRosterAttendanceService::class)->markPresent(
            $this->selectedDate,
            ['VHS TEACHER']
        );

        $this->assertSame(1, $result['skipped_pre_doj']);
        $this->assertSame(0, $result['marked_present']);
        $this->assertSame(0, EmployeeAttendance::count());
    }

    public function test_bulk_attendance_allows_configured_holiday(): void
    {
        $this->insertEmployee(7, 'FRONT-DESK', '2026-07-01');
        $this->insertRoster(7, 'FRONT-DESK', $this->selectedDate, '08:30', '17:30');
        DB::table('employee_holidays')->insert([
            'holiday_date' => $this->selectedDate->toDateString(),
            'name' => 'Test Holiday',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = app(EmployeeRosterAttendanceService::class)->markPresent(
            $this->selectedDate,
            ['FRONT-DESK']
        );

        $this->assertSame(0, $result['skipped_non_holidays']);
        $this->assertSame(1, $result['marked_present']);
        $this->assertSame(1, EmployeeAttendance::count());
    }

    public function test_holiday_does_not_create_fallback_roster_from_previous_timeslot(): void
    {
        $this->insertEmployee(8, 'GROUP-D', '2026-07-01');
        $this->insertRoster(
            8,
            'GROUP-D',
            $this->selectedDate->copy()->subDay(),
            '08:00',
            '20:00'
        );
        DB::table('employee_holidays')->insert([
            'holiday_date' => $this->selectedDate->toDateString(),
            'name' => 'Test Holiday',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = app(EmployeeRosterAttendanceService::class)->markPresent(
            $this->selectedDate,
            ['GROUP-D']
        );

        $this->assertSame(0, $result['fallback_created']);
        $this->assertSame(0, DB::table('employee_schedule_rosters')
            ->whereDate('roster_date', $this->selectedDate->toDateString())
            ->count());
        $this->assertSame(0, EmployeeAttendance::count());
    }

    public function test_inactive_holiday_and_future_date_cannot_generate_punches(): void
    {
        $this->insertRoster(1, 'VHS TEACHER', $this->selectedDate, '09:00', '15:00');
        $this->insertHoliday();
        DB::table('employee_holidays')->update(['status' => 0]);
        $service = app(EmployeeRosterAttendanceService::class);
        $result = $service->markPresent($this->selectedDate, ['VHS TEACHER']);
        $this->assertSame(1, $result['skipped_non_holidays']);
        $this->assertSame(0, EmployeeAttendance::count());

        $future = Carbon::today('Asia/Kolkata')->addDay();
        $this->insertRoster(2, 'VHS TEACHER', $future, '09:00', '15:00');
        DB::table('employee_holidays')->insert([
            'holiday_date' => $future->toDateString(), 'name' => 'Future Holiday', 'status' => 1,
        ]);
        $result = $service->markPresent($future, ['VHS TEACHER']);
        $this->assertSame(0, $result['marked_present']);
        $this->assertSame(0, EmployeeAttendance::count());
    }

    public function test_repair_command_previews_backs_up_and_only_resets_selected_photo_free_punch_outs(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        foreach ([1, 2, 3] as $id) {
            $roster = $this->insertRoster($id, 'VHS TEACHER', $this->selectedDate, '09:00', '15:00');
            EmployeeAttendance::create(array_merge(
                $this->attendanceSnapshot($roster, $id, 'VHS TEACHER', '09:00', '15:00'),
                [
                    'punch_in_at' => $this->selectedDate->copy()->setTime(9, 0),
                    'punch_in_image' => '/in.jpg',
                    'punch_out_at' => $this->selectedDate->copy()->setTime(15, 0),
                    'punch_out_image' => $id === 2 ? '/out.jpg' : null,
                    'punch_out_ip' => '127.0.0.1',
                    'punch_out_portal_branch_id' => 1,
                ]
            ));
        }
        $command = 'attendance:reset-photo-free-punch-outs';
        $this->artisan($command, ['date' => '2026-07-29'])->assertExitCode(0);
        $this->assertSame(3, EmployeeAttendance::whereNotNull('punch_out_at')->count());
        $this->artisan($command, ['date' => '2026-07-29', '--ids' => '1,2', '--apply' => true])->assertExitCode(0);
        $row = EmployeeAttendance::findOrFail(1);
        $this->assertNull($row->punch_out_at);
        $this->assertNull($row->punch_out_ip);
        $this->assertNull($row->punch_out_portal_branch_id);
        $this->assertSame('/in.jpg', $row->punch_in_image);
        $this->assertSame('09:00', $row->punch_in_at->format('H:i'));
        $this->assertSame(2, EmployeeAttendance::whereNotNull('punch_out_at')->count());
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        $files = $disk->files('attendance-repairs');
        $this->assertCount(1, $files);
        $backup = json_decode($disk->get($files[0]), true);
        $this->assertCount(1, $backup['records']);
        $this->assertNotNull($backup['records'][0]['punch_out_at']);
        $this->artisan($command, ['date' => '2026-07-29', '--ids' => '1,2', '--apply' => true])->assertExitCode(0);
        $this->assertCount(1, $disk->files('attendance-repairs'));
        $this->artisan($command, ['date' => '2026-02-30', '--apply' => true])->assertExitCode(1);
    }

    private function insertHoliday(?string $branch = null, ?string $category = null): void
    {
        DB::table('employee_holidays')->insert([
            'holiday_date' => $this->selectedDate->toDateString(),
            'name' => 'Test Holiday',
            'branch_name' => $branch,
            'category' => $category,
            'status' => 1,
        ]);
    }

    private function attendanceRequest(string $path): Request
    {
        $request = Request::create($path, 'POST', [
            'attendance_date' => $this->selectedDate->toDateString(),
        ]);
        $request->setLaravelSession(app('session')->driver());

        return $request;
    }

    private function insertEmployee(
        int $employeeId,
        string $category,
        ?string $dateOfJoining = null
    ): void
    {
        DB::table('employees')->insert([
            'id' => $employeeId,
            'employee_no' => 'NVGI-'.str_pad((string) $employeeId, 4, '0', STR_PAD_LEFT),
            'first_name' => 'EMPLOYEE',
            'last_name' => (string) $employeeId,
            'category' => json_encode([$category]),
            'doj' => $dateOfJoining,
            'status' => 1,
        ]);
    }

    private function insertRoster(
        int $employeeId,
        string $category,
        Carbon $rosterDate,
        string $inTime,
        string $outTime,
        int $branchId = 1,
        string $branchName = 'Bibirhat'
    ): int {
        return (int) DB::table('employee_schedule_rosters')->insertGetId([
            'employee_id' => $employeeId,
            'employee_no' => 'NVGI-'.str_pad((string) $employeeId, 4, '0', STR_PAD_LEFT),
            'employee_name' => 'EMPLOYEE '.$employeeId,
            'category' => $category,
            'unit_id' => 1,
            'unit_name' => 'NVGI',
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
        ]);
    }

    private function attendanceSnapshot(
        int $rosterId,
        int $employeeId,
        string $category,
        string $inTime,
        string $outTime
    ): array {
        return [
            'roster_id' => $rosterId,
            'employee_id' => $employeeId,
            'employee_no' => 'NVGI-'.str_pad((string) $employeeId, 4, '0', STR_PAD_LEFT),
            'employee_name' => 'EMPLOYEE '.$employeeId,
            'category' => $category,
            'unit_id' => 1,
            'branch_id' => 1,
            'branch_name' => 'Bibirhat',
            'attendance_date' => $this->selectedDate->toDateString(),
            'scheduled_in_time' => $inTime,
            'scheduled_out_time' => $outTime,
        ];
    }
}
