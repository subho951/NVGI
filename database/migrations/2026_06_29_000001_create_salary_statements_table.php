<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('salary_statements')) {
            return;
        }

        Schema::create('salary_statements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salary_generation_id')->nullable()->unique();
            $table->tinyInteger('salary_month')->index();
            $table->unsignedSmallInteger('salary_year')->index();
            $table->string('branch_name', 120)->index();
            $table->unsignedBigInteger('employee_id')->index();
            $table->string('employee_no')->nullable();
            $table->string('employee_name')->nullable();
            $table->string('employee_category', 80)->nullable()->index();
            $table->decimal('salary_amount', 14, 2)->default(0);
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('account_no')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('account_type', 40)->nullable();
            $table->tinyInteger('status')->default(1)->index();
            $table->integer('generated_by')->default(0);
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->unique(
                ['salary_month', 'salary_year', 'branch_name', 'employee_id', 'employee_category'],
                'salary_statements_period_employee_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_statements');
    }
};
