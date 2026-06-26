<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employee_leave_taken_histories')) {
            return;
        }

        Schema::create('employee_leave_taken_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id')->index();
            $table->text('employee_no')->nullable();
            $table->text('employee_name')->nullable();
            $table->unsignedBigInteger('leave_type_id')->index();
            $table->date('leave_date')->index();
            $table->decimal('leave_count', 8, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->tinyInteger('status')->default(1)->index();
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->index(
                ['employee_id', 'leave_type_id', 'leave_date'],
                'employee_leave_taken_lookup'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leave_taken_histories');
    }
};
