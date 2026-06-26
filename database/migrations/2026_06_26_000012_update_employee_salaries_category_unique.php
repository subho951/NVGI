<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employee_salaries')) {
            return;
        }

        if ($this->indexExists('employee_salaries', 'employee_salaries_employee_head_unique')) {
            Schema::table('employee_salaries', function (Blueprint $table) {
                $table->dropUnique('employee_salaries_employee_head_unique');
            });
        }

        if (! $this->indexExists('employee_salaries', 'employee_salaries_employee_category_head_unique')) {
            Schema::table('employee_salaries', function (Blueprint $table) {
                $table->unique(['employee_id', 'employee_category', 'salary_head_id'], 'employee_salaries_employee_category_head_unique');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('employee_salaries')) {
            return;
        }

        if ($this->indexExists('employee_salaries', 'employee_salaries_employee_category_head_unique')) {
            Schema::table('employee_salaries', function (Blueprint $table) {
                $table->dropUnique('employee_salaries_employee_category_head_unique');
            });
        }

        $hasDuplicateEmployeeHeads = DB::table('employee_salaries')
            ->select('employee_id', 'salary_head_id')
            ->groupBy('employee_id', 'salary_head_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if (! $hasDuplicateEmployeeHeads && ! $this->indexExists('employee_salaries', 'employee_salaries_employee_head_unique')) {
            Schema::table('employee_salaries', function (Blueprint $table) {
                $table->unique(['employee_id', 'salary_head_id'], 'employee_salaries_employee_head_unique');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return ! empty(DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]));
    }
};
