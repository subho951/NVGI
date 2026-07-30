<?php

namespace Tests\Feature;

use App\Http\Controllers\LeaveApplicationController;
use App\Models\Employee;
use App\Models\EmployeeLeaveAllotment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use ReflectionMethod;
use Tests\TestCase;

class LeaveApplicationIntegrityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('employee_leave_allotments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_allotment_id')->default(1);
            $table->integer('employee_id');
            $table->unsignedBigInteger('leave_type_id');
            $table->date('leave_tenure_from');
            $table->date('leave_tenure_to');
            $table->decimal('total_allotment', 8, 2)->default(0);
            $table->decimal('used_leave', 8, 2)->default(0);
            $table->decimal('balance_leave', 8, 2)->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::create('employee_leave_taken_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_application_id')->nullable()->unique();
            $table->integer('employee_id');
            $table->unsignedBigInteger('leave_type_id');
            $table->date('leave_date');
            $table->decimal('leave_count', 8, 2)->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->unsignedBigInteger('leave_type_id');
            $table->date('leave_from_date');
            $table->date('leave_to_date');
            $table->decimal('no_of_days', 8, 2);
            $table->tinyInteger('application_status')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function test_leave_days_are_derived_from_selected_dates(): void
    {
        $days = $this->invokeControllerMethod(
            'calculatedLeaveDays',
            ['2026-08-05', '2026-08-11']
        );

        $this->assertSame(7.0, $days);
    }

    public function test_pending_or_approved_overlapping_leave_is_rejected(): void
    {
        DB::table('leave_applications')->insert([
            'employee_id' => 7,
            'leave_type_id' => 1,
            'leave_from_date' => '2026-08-05',
            'leave_to_date' => '2026-08-11',
            'no_of_days' => 7,
            'application_status' => 1,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(ValidationException::class);

        $this->invokeControllerMethod('validateNoOverlappingApplication', [
            7,
            1,
            '2026-08-05',
            '2026-08-11',
        ]);
    }

    public function test_available_balance_is_reconstructed_from_history_instead_of_stale_balance_column(): void
    {
        $allotmentId = DB::table('employee_leave_allotments')->insertGetId([
            'employee_id' => 7,
            'leave_type_id' => 1,
            'leave_tenure_from' => '2026-01-01',
            'leave_tenure_to' => '2026-12-31',
            'total_allotment' => 12,
            'used_leave' => 0,
            'balance_leave' => 12,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('employee_leave_taken_histories')->insert([
            'leave_application_id' => 9,
            'employee_id' => 7,
            'leave_type_id' => 1,
            'leave_date' => '2026-08-05',
            'leave_count' => 7,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $balance = $this->invokeControllerMethod('availableLeaveBalance', [
            EmployeeLeaveAllotment::findOrFail($allotmentId),
        ]);

        $this->assertSame(5.0, $balance);
    }

    public function test_tsa_only_employee_is_not_leave_eligible(): void
    {
        $tsaEmployee = new Employee([
            'category' => json_encode(['TSA TEACHER']),
        ]);
        $vhsEmployee = new Employee([
            'category' => json_encode(['VHS TEACHER']),
        ]);

        $this->assertFalse($this->invokeControllerMethod(
            'employeeHasLeaveEligibleCategory',
            [$tsaEmployee]
        ));
        $this->assertTrue($this->invokeControllerMethod(
            'employeeHasLeaveEligibleCategory',
            [$vhsEmployee]
        ));
    }

    private function invokeControllerMethod(string $method, array $arguments)
    {
        $reflection = new ReflectionMethod(LeaveApplicationController::class, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs(app(LeaveApplicationController::class), $arguments);
    }
}
