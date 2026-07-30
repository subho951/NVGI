<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PayrollDataMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('employees', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('employee_no');
            $table->text('category')->nullable();
        });

        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->text('employee_no')->nullable();
            $table->text('employee_name')->nullable();
            $table->unsignedBigInteger('leave_type_id');
            $table->date('leave_from_date');
            $table->date('leave_to_date');
            $table->decimal('no_of_days', 8, 2);
            $table->tinyInteger('application_status')->default(0);
            $table->unsignedBigInteger('leave_taken_history_id')->nullable();
            $table->integer('approved_by')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::create('employee_leave_taken_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->text('employee_no')->nullable();
            $table->text('employee_name')->nullable();
            $table->unsignedBigInteger('leave_type_id');
            $table->date('leave_date');
            $table->decimal('leave_count', 8, 2);
            $table->text('remarks')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->timestamps();
        });

        Schema::create('employee_leave_allotments', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->unsignedBigInteger('leave_type_id');
            $table->date('leave_tenure_from');
            $table->date('leave_tenure_to');
            $table->decimal('total_allotment', 8, 2);
            $table->decimal('used_leave', 8, 2);
            $table->decimal('balance_leave', 8, 2);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::create('leave_allotments', function (Blueprint $table) {
            $table->id();
            $table->decimal('tsa_teacher_leave_count', 8, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('status')->default(1);
            $table->integer('created_by')->default(1);
            $table->integer('updated_by')->default(1);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('module_id')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function test_known_duplicate_leave_and_tsa_allotments_are_repaired(): void
    {
        DB::table('employees')->insert([
            [
                'id' => 7,
                'employee_no' => 'NVGI-0007',
                'category' => json_encode(['VHS TEACHER']),
            ],
            [
                'id' => 20,
                'employee_no' => 'NVGI-0020',
                'category' => json_encode(['TSA TEACHER']),
            ],
        ]);
        DB::table('leave_applications')->insert([
            [
                'id' => 9,
                'employee_id' => 7,
                'employee_no' => 'NVGI-0007',
                'employee_name' => 'RIDDHI CHAKRABORTY',
                'leave_type_id' => 1,
                'leave_from_date' => '2026-08-05',
                'leave_to_date' => '2026-08-11',
                'no_of_days' => 7,
                'application_status' => 1,
                'leave_taken_history_id' => 6,
                'approved_by' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'employee_id' => 7,
                'employee_no' => 'NVGI-0007',
                'employee_name' => 'RIDDHI CHAKRABORTY',
                'leave_type_id' => 1,
                'leave_from_date' => '2026-08-05',
                'leave_to_date' => '2026-08-11',
                'no_of_days' => 7,
                'application_status' => 1,
                'leave_taken_history_id' => 2,
                'approved_by' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        DB::table('employee_leave_taken_histories')->insert([
            'id' => 2,
            'employee_id' => 7,
            'employee_no' => 'NVGI-0007',
            'employee_name' => 'RIDDHI CHAKRABORTY',
            'leave_type_id' => 1,
            'leave_date' => '2026-08-05',
            'leave_count' => 7,
            'remarks' => 'Legacy duplicate leave history',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('employee_leave_allotments')->insert([
            [
                'employee_id' => 7,
                'leave_type_id' => 1,
                'leave_tenure_from' => '2026-01-01',
                'leave_tenure_to' => '2026-12-31',
                'total_allotment' => 11,
                'used_leave' => 14,
                'balance_leave' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 20,
                'leave_type_id' => 1,
                'leave_tenure_from' => '2026-01-01',
                'leave_tenure_to' => '2026-12-31',
                'total_allotment' => 12,
                'used_leave' => 0,
                'balance_leave' => 12,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        DB::table('leave_allotments')->insert([
            'id' => 1,
            'tsa_teacher_leave_count' => 12,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('roles')->insert([
            [
                'id' => 1,
                'name' => 'Master Admin',
                'module_id' => json_encode(['6']),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Payroll Only',
                'module_id' => json_encode(['32']),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        (require database_path('migrations/2026_07_30_000001_create_employee_holidays_table.php'))->up();
        (require database_path('migrations/2026_07_30_000002_add_payroll_integrity_fields.php'))->up();
        (require database_path('migrations/2026_07_30_000003_repair_riddhi_duplicate_leave.php'))->up();
        (require database_path('migrations/2026_07_30_000004_disable_tsa_leave_allotments.php'))->up();
        (require database_path('migrations/2026_07_30_000005_add_holiday_management_module.php'))->up();

        $this->assertDatabaseHas('leave_applications', [
            'id' => 9,
            'leave_taken_history_id' => 2,
            'status' => 1,
        ]);
        $this->assertDatabaseHas('leave_applications', [
            'id' => 10,
            'status' => 3,
        ]);
        $this->assertDatabaseHas('employee_leave_taken_histories', [
            'id' => 2,
            'leave_application_id' => 9,
            'leave_count' => 7,
            'status' => 1,
        ]);
        $this->assertDatabaseHas('employee_leave_allotments', [
            'employee_id' => 7,
            'used_leave' => 7,
            'balance_leave' => 4,
        ]);
        $this->assertDatabaseHas('employee_leave_allotments', [
            'employee_id' => 20,
            'status' => 3,
        ]);
        $this->assertDatabaseHas('leave_allotments', [
            'id' => 1,
            'tsa_teacher_leave_count' => 0,
        ]);
        $this->assertDatabaseHas('employee_holidays', [
            'holiday_date' => '2026-07-06',
            'status' => 1,
        ]);
        $this->assertDatabaseHas('employee_holidays', [
            'holiday_date' => '2026-07-16',
            'status' => 1,
        ]);
        $this->assertDatabaseHas('modules', [
            'id' => 40,
            'name' => 'Masters - Holiday Management',
            'status' => 1,
        ]);
        $masterModuleIds = json_decode(
            DB::table('roles')->where('id', 1)->value('module_id'),
            true
        );
        $payrollModuleIds = json_decode(
            DB::table('roles')->where('id', 2)->value('module_id'),
            true
        );
        $this->assertContains('40', $masterModuleIds);
        $this->assertNotContains('40', $payrollModuleIds);
    }
}
