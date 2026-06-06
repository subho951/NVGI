<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('employee_attendances', 'is_absent')) {
            Schema::table('employee_attendances', function (Blueprint $table) {
                $table->boolean('is_absent')->default(false)->index()->after('late_minutes');
            });
        }

        if (! Schema::hasColumn('employee_attendances', 'absent_marked_at')) {
            Schema::table('employee_attendances', function (Blueprint $table) {
                $table->timestamp('absent_marked_at')->nullable()->after('is_absent');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('employee_attendances', 'absent_marked_at')) {
            Schema::table('employee_attendances', function (Blueprint $table) {
                $table->dropColumn('absent_marked_at');
            });
        }

        if (Schema::hasColumn('employee_attendances', 'is_absent')) {
            Schema::table('employee_attendances', function (Blueprint $table) {
                $table->dropColumn('is_absent');
            });
        }
    }
};
