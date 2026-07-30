<?php

namespace Tests\Feature;

use App\Http\Controllers\HolidayController;
use App\Models\EmployeeHoliday;
use App\Services\EmployeeHolidayService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HolidayManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 7, 30, 18, 0, 0, 'Asia/Kolkata'));

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('status')->default(1);
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::create('employee_holidays', function (Blueprint $table) {
            $table->id();
            $table->date('holiday_date');
            $table->string('name', 160);
            $table->string('branch_name')->nullable();
            $table->string('category')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->date('doj')->nullable();
        });

        Schema::create('employee_schedule_rosters', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->text('employee_no')->nullable();
            $table->text('employee_name')->nullable();
            $table->string('category');
            $table->integer('unit_id')->nullable();
            $table->integer('branch_id');
            $table->text('branch_name')->nullable();
            $table->date('roster_date');
            $table->string('in_time', 5)->nullable();
            $table->string('out_time', 5)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::create('employee_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('roster_id')->unique();
            $table->boolean('is_absent')->default(false);
            $table->timestamp('absent_marked_at')->nullable();
            $table->timestamp('punch_in_at')->nullable();
            $table->timestamps();
        });

        DB::table('branches')->insert([
            ['id' => 1, 'name' => 'Bibirhat', 'status' => 1],
            ['id' => 2, 'name' => 'Mukundapur', 'status' => 1],
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_holiday_management_routes_are_registered_under_authenticated_admin_routes(): void
    {
        $routes = app('router')->getRoutes();

        foreach ([
            'holiday.list',
            'holiday.add',
            'holiday.edit',
            'holiday.change-status',
            'holiday.delete',
        ] as $routeName) {
            $this->assertNotNull($routes->getByName($routeName));
        }
    }

    public function test_global_and_branch_specific_holidays_can_be_added_for_same_date(): void
    {
        $controller = app(HolidayController::class);

        $controller->add($this->holidayRequest([
            'holiday_date' => '2026-08-15',
            'name' => 'Independence Day',
            'branch_name' => '',
            'category' => '',
        ]));
        $controller->add($this->holidayRequest([
            'holiday_date' => '2026-08-15',
            'name' => 'Bibirhat Holiday',
            'branch_name' => 'Bibirhat',
            'category' => '',
        ]));

        $this->assertSame(2, EmployeeHoliday::count());
        $this->assertSame(1, EmployeeHoliday::whereDate('holiday_date', '2026-08-15')
            ->whereNull('branch_name')
            ->whereNull('category')
            ->where('status', 1)
            ->count());
        $this->assertSame(1, EmployeeHoliday::whereDate('holiday_date', '2026-08-15')
            ->where('branch_name', 'Bibirhat')
            ->whereNull('category')
            ->where('status', 1)
            ->count());
    }

    public function test_duplicate_date_branch_and_category_scope_is_rejected(): void
    {
        $controller = app(HolidayController::class);
        $requestData = [
            'holiday_date' => '2026-08-15',
            'name' => 'Independence Day',
            'branch_name' => '',
            'category' => '',
        ];

        $controller->add($this->holidayRequest($requestData));

        $this->expectException(ValidationException::class);
        $controller->add($this->holidayRequest($requestData));
    }

    public function test_holiday_service_honours_branch_and_category_scope(): void
    {
        EmployeeHoliday::create([
            'holiday_date' => '2026-08-20',
            'name' => 'Bibirhat VHS Holiday',
            'branch_name' => 'Bibirhat',
            'category' => 'VHS TEACHER',
            'status' => 1,
        ]);
        $service = app(EmployeeHolidayService::class);
        $date = Carbon::create(2026, 8, 20);

        $this->assertSame(1, $service->forPeriod($date, $date)->count());
        $this->assertTrue($service->applies($date, 'Bibirhat', 'VHS TEACHER'));
        $this->assertFalse($service->applies($date, 'Mukundapur', 'VHS TEACHER'));
        $this->assertFalse($service->applies($date, 'Bibirhat', 'TSA TEACHER'));
    }

    public function test_adding_holiday_clears_existing_derived_absence_without_punch(): void
    {
        DB::table('employees')->insert([
            'id' => 1,
            'doj' => '2026-07-01',
        ]);
        $rosterId = DB::table('employee_schedule_rosters')->insertGetId([
            'employee_id' => 1,
            'employee_no' => 'NVGI-0001',
            'employee_name' => 'TEST EMPLOYEE',
            'category' => 'VHS TEACHER',
            'branch_id' => 1,
            'branch_name' => 'Bibirhat',
            'roster_date' => '2026-07-15',
            'in_time' => '10:00',
            'out_time' => '15:00',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('employee_attendances')->insert([
            'roster_id' => $rosterId,
            'is_absent' => 1,
            'absent_marked_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(HolidayController::class)->add($this->holidayRequest([
            'holiday_date' => '2026-07-15',
            'name' => 'New Holiday',
            'branch_name' => '',
            'category' => '',
        ]));

        $this->assertDatabaseHas('employee_attendances', [
            'roster_id' => $rosterId,
            'is_absent' => 0,
            'absent_marked_at' => null,
        ]);
    }

    private function holidayRequest(array $data): Request
    {
        $request = Request::create('/holiday/add', 'POST', $data);
        $request->setLaravelSession(app('session')->driver());

        return $request;
    }
}
