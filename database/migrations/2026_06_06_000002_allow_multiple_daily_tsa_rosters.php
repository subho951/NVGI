<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_schedule_rosters', function (Blueprint $table) {
            $table->dropUnique('employee_schedule_roster_unique');
            $table->unique(
                ['employee_id', 'category', 'branch_id', 'roster_date', 'in_time'],
                'employee_schedule_roster_time_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('employee_schedule_rosters', function (Blueprint $table) {
            $table->dropUnique('employee_schedule_roster_time_unique');
            $table->unique(
                ['employee_id', 'category', 'branch_id', 'roster_date'],
                'employee_schedule_roster_unique'
            );
        });
    }
};
