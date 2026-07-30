<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('leave_allotments')
            && Schema::hasColumn('leave_allotments', 'tsa_teacher_leave_count')
        ) {
            DB::table('leave_allotments')->update([
                'tsa_teacher_leave_count' => 0,
                'updated_at' => now(),
            ]);
        }

        if (
            ! Schema::hasTable('employees')
            || ! Schema::hasTable('employee_leave_allotments')
        ) {
            return;
        }

        DB::table('employees')
            ->select(['id', 'category'])
            ->orderBy('id')
            ->get()
            ->each(function ($employee) {
                $decodedCategories = json_decode((string) $employee->category, true);
                $categories = is_array($decodedCategories)
                    ? $decodedCategories
                    : preg_split('/\s*,\s*/', (string) $employee->category);
                $categories = collect($categories)
                    ->map(fn ($category) => strtoupper(trim((string) $category)))
                    ->filter()
                    ->unique();

                if (
                    ! $categories->contains('TSA TEACHER')
                    || $categories->intersect([
                        'VHS TEACHER',
                        'FRONT-DESK',
                        'GROUP-D',
                    ])->isNotEmpty()
                ) {
                    return;
                }

                DB::table('employee_leave_allotments')
                    ->where('employee_id', '=', (int) $employee->id)
                    ->where('status', '!=', 3)
                    ->update([
                        'status' => 3,
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        // Existing TSA leave values cannot be reconstructed safely.
    }
};
