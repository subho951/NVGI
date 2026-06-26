<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employee_leave_allotments')) {
            return;
        }

        Schema::create('employee_leave_allotments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_allotment_id')->index();
            $table->integer('employee_id')->index();
            $table->text('employee_no')->nullable();
            $table->text('employee_name')->nullable();
            $table->text('employee_category')->nullable();
            $table->unsignedBigInteger('leave_type_id')->index();
            $table->date('leave_tenure_from')->index();
            $table->date('leave_tenure_to')->index();
            $table->decimal('current_allotment', 8, 2)->default(0);
            $table->decimal('previous_balance', 8, 2)->default(0);
            $table->decimal('total_allotment', 8, 2)->default(0);
            $table->decimal('used_leave', 8, 2)->default(0);
            $table->decimal('balance_leave', 8, 2)->default(0);
            $table->tinyInteger('status')->default(1)->index();
            $table->integer('assigned_by')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->unique(
                ['leave_allotment_id', 'employee_id'],
                'employee_leave_allotment_employee_unique'
            );
            $table->index(
                ['employee_id', 'leave_type_id', 'leave_tenure_to'],
                'employee_leave_allotment_balance_lookup'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leave_allotments');
    }
};
