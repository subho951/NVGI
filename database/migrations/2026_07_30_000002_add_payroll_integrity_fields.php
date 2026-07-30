<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('salary_generations')) {
            Schema::table('salary_generations', function (Blueprint $table) {
                if (! Schema::hasColumn('salary_generations', 'salary_period_start')) {
                    $table->date('salary_period_start')->nullable()->after('doj');
                }

                if (! Schema::hasColumn('salary_generations', 'salary_period_end')) {
                    $table->date('salary_period_end')->nullable()->after('salary_period_start');
                }

                if (! Schema::hasColumn('salary_generations', 'eligible_days')) {
                    $table->decimal('eligible_days', 8, 2)->default(0)->after('salary_period_end');
                }

                if (! Schema::hasColumn('salary_generations', 'payable_gross_salary')) {
                    $table->decimal('payable_gross_salary', 14, 2)->default(0)->after('gross_salary');
                }

                if (! Schema::hasColumn('salary_generations', 'approved_leave_days')) {
                    $table->decimal('approved_leave_days', 8, 2)->default(0)->after('unpaid_absent_days');
                }

                if (! Schema::hasColumn('salary_generations', 'holiday_days')) {
                    $table->decimal('holiday_days', 8, 2)->default(0)->after('approved_leave_days');
                }

                if (! Schema::hasColumn('salary_generations', 'pre_doj_excluded_days')) {
                    $table->decimal('pre_doj_excluded_days', 8, 2)->default(0)->after('holiday_days');
                }

                if (! Schema::hasColumn('salary_generations', 'late_amount')) {
                    $table->decimal('late_amount', 14, 2)->default(0)->after('late_penalty_units');
                }

                if (! Schema::hasColumn('salary_generations', 'attendance_details')) {
                    $table->longText('attendance_details')->nullable()->after('leave_details');
                }
            });
        }

        if (
            Schema::hasTable('employee_leave_taken_histories')
            && ! Schema::hasColumn('employee_leave_taken_histories', 'leave_application_id')
        ) {
            Schema::table('employee_leave_taken_histories', function (Blueprint $table) {
                $table->unsignedBigInteger('leave_application_id')->nullable()->after('id');
            });

            if (Schema::hasTable('leave_applications')) {
                DB::table('leave_applications')
                    ->whereNotNull('leave_taken_history_id')
                    ->orderBy('id')
                    ->get()
                    ->each(function ($application) {
                        DB::table('employee_leave_taken_histories')
                            ->where('id', '=', (int) $application->leave_taken_history_id)
                            ->whereNull('leave_application_id')
                            ->update([
                                'leave_application_id' => (int) $application->id,
                            ]);
                    });
            }

            Schema::table('employee_leave_taken_histories', function (Blueprint $table) {
                $table->unique(
                    'leave_application_id',
                    'employee_leave_history_application_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('employee_leave_taken_histories')
            && Schema::hasColumn('employee_leave_taken_histories', 'leave_application_id')
        ) {
            Schema::table('employee_leave_taken_histories', function (Blueprint $table) {
                $table->dropUnique('employee_leave_history_application_unique');
                $table->dropColumn('leave_application_id');
            });
        }

        if (Schema::hasTable('salary_generations')) {
            Schema::table('salary_generations', function (Blueprint $table) {
                foreach ([
                    'attendance_details',
                    'late_amount',
                    'pre_doj_excluded_days',
                    'holiday_days',
                    'approved_leave_days',
                    'payable_gross_salary',
                    'eligible_days',
                    'salary_period_end',
                    'salary_period_start',
                ] as $column) {
                    if (Schema::hasColumn('salary_generations', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
