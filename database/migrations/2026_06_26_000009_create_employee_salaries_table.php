<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employee_salaries')) {
            return;
        }

        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->index();
            $table->string('employee_no')->nullable();
            $table->string('employee_name')->nullable();
            $table->string('employee_category')->nullable();
            $table->decimal('gross_salary', 14, 2)->default(0);
            $table->unsignedBigInteger('salary_head_id')->index();
            $table->string('salary_head_name');
            $table->string('salary_head_type', 20)->index();
            $table->string('calculation_type', 20);
            $table->string('calculation_base', 80);
            $table->decimal('calculation_amount', 14, 2)->default(0);
            $table->string('formula_label')->nullable();
            $table->boolean('is_payslip_show')->default(1);
            $table->decimal('calculated_amount', 14, 2)->default(0);
            $table->tinyInteger('status')->default(1)->index();
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->integer('calculated_by')->default(0);
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->unique(['employee_id', 'salary_head_id'], 'employee_salaries_employee_head_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_salaries');
    }
};
