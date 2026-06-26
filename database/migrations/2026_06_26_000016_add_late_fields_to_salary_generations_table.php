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
            if (! Schema::hasColumn('salary_generations', 'late_count')) {
                $table->unsignedSmallInteger('late_count')->default(0)->after('attendance_hours');
            }

            if (! Schema::hasColumn('salary_generations', 'late_penalty_units')) {
                $table->decimal('late_penalty_units', 8, 2)->default(0)->after('late_count');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('salary_generations')) {
            return;
        }

        Schema::table('salary_generations', function (Blueprint $table) {
            foreach (['late_penalty_units', 'late_count'] as $column) {
                if (Schema::hasColumn('salary_generations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
