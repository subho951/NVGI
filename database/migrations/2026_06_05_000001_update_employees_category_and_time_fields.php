<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employees')) {
            return;
        }

        DB::statement('ALTER TABLE employees MODIFY category TEXT NULL');

        if (!Schema::hasColumn('employees', 'in_time')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->text('in_time')->nullable()->after('category');
            });
        }

        if (!Schema::hasColumn('employees', 'out_time')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->text('out_time')->nullable()->after('in_time');
            });
        }
    }

    public function down(): void
    {
        // Multi-category JSON values cannot be safely converted back to the old enum.
    }
};
