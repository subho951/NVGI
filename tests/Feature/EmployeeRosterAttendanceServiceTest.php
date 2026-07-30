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

    public function test_missing_tsa_roster_copies_all_latest_previous_timeslots_and_is_idempotent(): void
    {
        $this->insertEmployee(2, 'TSA TEACHER');
        $previousDate = $this->selectedDate->copy()->subDay();
        $this->insertRoster(2, 'TSA TEACHER', $previousDate, '09:00', '11:00', 1, 'Bibirhat');
        $this->insertRoster(2, 'TSA TEACHER', $previousDate, '14:00', '16:00', 2, 'Mukundapur');

        $service = app(EmployeeRosterAttendanceService::class);
        $firstResult = $service->markPresent($this->selectedDate, ['TSA TEACHER'], 99);

        $this->assertSame(2, $firstResult['fallback_created']);
        $this->assertSame(2, $firstResult['marked_present']);
        $this->assertSame(2, DB::table('employee_schedule_rosters')
            ->where('roster_date', '2026-07-29')
            ->where('category', 'TSA TEACHER')
            ->count());
        $this->assertSame(2, EmployeeAttendance::count());
        $this->assertDatabaseHas('employee_schedule_rosters', [
            'employee_id' => 2,
            'branch_id' => 1,
            'roster_date' => '2026-07-29',
            'in_time' => '09:00',
            'out_time' => '11:00',
            'created_by' => 99,
        ]);
        $this->assertDatabaseHas('employee_schedule_rosters', [
            'employee_id' => 2,
            'branch_id' => 2,
            'roster_date' => '2026-07-29',
            'in_time' => '14:00',
            'out_time' => '16:00',
            'created_by' => 99,
        ]);

        $secondResult = $service->markPresent($this->selectedDate, ['TSA TEACHER'], 99);

        $this->assertSame(0, $secondResult['fallback_created']);
        $this->assertSame(0, $secondResult['marked_present']);
        $this->assertSame(2, $secondResult['already_completed']);
        $this->assertSame(4, DB::table('employee_schedule_rosters')->count());
        $this->assertSame(2, EmployeeAttendance::count());
    }

    public function test_existing_real_punch_is_preserved_and_absent_attendance_is_converted(): void
    {
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

        $this->assertSame(1, $result['skipped_employees']);
        $this->assertSame(0, $result['roster_assignments']);
        $this->assertSame(0, DB::table('employee_schedule_rosters')->count());
        $this->assertSame(0, EmployeeAttendance::count());
    }

    private function attendanceRequest(string $path): Request
    {
        $request = Request::create($path, 'POST', [
            'attendance_date' => $this->selectedDate->toDateString(),
        ]);
        $request->setLaravelSession(app('session')->driver());

        return $request;
    }

    private function insertEmployee(int $employeeId, string $category): void
    {
        DB::table('employees')->insert([
            'id' => $employeeId,
            'employee_no' => 'NVGI-'.str_pad((string) $employeeId, 4, '0', STR_PAD_LEFT),
            'first_name' => 'EMPLOYEE',
            'last_name' => (string) $employeeId,
            'category' => json_encode([$category]),
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
