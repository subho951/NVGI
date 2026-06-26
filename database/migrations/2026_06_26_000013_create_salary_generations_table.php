<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('salary_generations')) {
            return;
        }

        Schema::create('salary_generations', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('salary_month')->index();
            $table->unsignedSmallInteger('salary_year')->index();
            $table->string('branch_name', 120)->index();
            $table->unsignedBigInteger('employee_id')->index();
            $table->string('employee_no')->nullable();
            $table->string('employee_name')->nullable();
            $table->string('employee_category', 80)->nullable()->index();
            $table->date('doj')->nullable();
            $table->decimal('gross_salary', 14, 2)->default(0);
            $table->decimal('earning_total', 14, 2)->default(0);
            $table->decimal('deduction_total', 14, 2)->default(0);
            $table->decimal('net_salary', 14, 2)->default(0);
            $table->decimal('cl_alloted', 8, 2)->default(0);
            $table->decimal('cl_balance', 8, 2)->default(0);
            $table->decimal('ml_alloted', 8, 2)->default(0);
            $table->decimal('ml_balance', 8, 2)->default(0);
            $table->longText('salary_head_details')->nullable();
            $table->tinyInteger('status')->default(1)->index();
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->unique(
                ['salary_month', 'salary_year', 'branch_name', 'employee_id', 'employee_category'],
                'salary_generations_period_employee_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_generations');
    }
};
