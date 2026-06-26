<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employees') || Schema::hasColumn('employees', 'category_salaries')) {
            return;
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->text('category_salaries')->nullable()->after('salary');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('employees') || ! Schema::hasColumn('employees', 'category_salaries')) {
            return;
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('category_salaries');
        });
    }
};
