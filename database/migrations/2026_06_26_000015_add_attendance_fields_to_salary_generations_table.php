<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('salary_generations')) {
            return;
        }

        Schema::table('salary_generations', function (Blueprint $table) {
            if (! Schema::hasColumn('salary_generations', 'absent_days')) {
                $table->decimal('absent_days', 8, 2)->default(0)->after('net_salary');
            }

            if (! Schema::hasColumn('salary_generations', 'unpaid_absent_days')) {
                $table->decimal('unpaid_absent_days', 8, 2)->default(0)->after('absent_days');
            }

            if (! Schema::hasColumn('salary_generations', 'absent_amount')) {
                $table->decimal('absent_amount', 14, 2)->default(0)->after('unpaid_absent_days');
            }

            if (! Schema::hasColumn('salary_generations', 'assigned_hours')) {
                $table->decimal('assigned_hours', 10, 2)->default(0)->after('absent_amount');
            }

            if (! Schema::hasColumn('salary_generations', 'attendance_hours')) {
                $table->decimal('attendance_hours', 10, 2)->default(0)->after('assigned_hours');
            }

            if (! Schema::hasColumn('salary_generations', 'leave_details')) {
                $table->longText('leave_details')->nullable()->after('ml_balance');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('salary_generations')) {
            return;
        }

        Schema::table('salary_generations', function (Blueprint $table) {
            foreach (['leave_details', 'attendance_hours', 'assigned_hours', 'absent_amount', 'unpaid_absent_days', 'absent_days'] as $column) {
                if (Schema::hasColumn('salary_generations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
