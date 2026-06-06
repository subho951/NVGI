<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employee_attendances')) {
            return;
        }

        Schema::create('employee_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('roster_id')->unique();
            $table->integer('employee_id')->index();
            $table->text('employee_no')->nullable();
            $table->text('employee_name')->nullable();
            $table->string('category', 100)->index();
            $table->integer('unit_id')->nullable()->index();
            $table->integer('branch_id')->index();
            $table->text('branch_name')->nullable();
            $table->date('attendance_date')->index();
            $table->string('scheduled_in_time', 5)->nullable();
            $table->string('scheduled_out_time', 5)->nullable();
            $table->timestamp('punch_in_at')->nullable()->index();
            $table->text('punch_in_image')->nullable();
            $table->string('punch_in_ip', 45)->nullable();
            $table->integer('punch_in_portal_branch_id')->nullable()->index();
            $table->timestamp('punch_out_at')->nullable()->index();
            $table->text('punch_out_image')->nullable();
            $table->string('punch_out_ip', 45)->nullable();
            $table->integer('punch_out_portal_branch_id')->nullable()->index();
            $table->timestamps();

            $table->index(
                ['attendance_date', 'branch_id', 'employee_id'],
                'employee_attendance_date_branch_employee'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_attendances');
    }
};
