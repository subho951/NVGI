<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('salary_heads') || Schema::hasColumn('salary_heads', 'is_payslip_show')) {
            return;
        }

        Schema::table('salary_heads', function (Blueprint $table) {
            $table->boolean('is_payslip_show')->default(1)->after('calculation_amount');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('salary_heads') || ! Schema::hasColumn('salary_heads', 'is_payslip_show')) {
            return;
        }

        Schema::table('salary_heads', function (Blueprint $table) {
            $table->dropColumn('is_payslip_show');
        });
    }
};
