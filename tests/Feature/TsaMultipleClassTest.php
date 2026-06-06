<?php

namespace Tests\Feature;

use App\Http\Controllers\EmployeeScheduleRosterController;
use App\Models\EmployeeScheduleRoster;
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
            'id' => 1,
            'unit_id' => 2,
            'name' => 'Bibirhat',
            'status' => 1,
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
}
