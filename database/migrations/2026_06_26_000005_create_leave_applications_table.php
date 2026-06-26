<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leave_applications')) {
            return;
        }

        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id')->index();
            $table->text('employee_no')->nullable();
            $table->text('employee_name')->nullable();
            $table->text('employee_category')->nullable();
            $table->unsignedBigInteger('leave_type_id')->index();
            $table->text('leave_type_name')->nullable();
            $table->date('leave_from_date')->index();
            $table->date('leave_to_date')->index();
            $table->decimal('no_of_days', 8, 2)->default(0);
            $table->date('apply_date')->index();
            $table->text('remarks')->nullable();
            $table->tinyInteger('application_status')->default(0)->index();
            $table->timestamp('approved_at')->nullable();
            $table->integer('approved_by')->default(0);
            $table->timestamp('rejected_at')->nullable();
            $table->integer('rejected_by')->default(0);
            $table->text('reject_reason')->nullable();
            $table->unsignedBigInteger('leave_taken_history_id')->nullable()->index();
            $table->tinyInteger('status')->default(1)->index();
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->index(
                ['employee_id', 'leave_type_id', 'application_status'],
                'leave_applications_employee_status_lookup'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
    }
};
