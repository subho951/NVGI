<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('transactions') && !Schema::hasColumn('transactions', 'ledger_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('ledger_id')->nullable()->after('type');
                $table->index('ledger_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'ledger_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex(['ledger_id']);
                $table->dropColumn('ledger_id');
            });
        }
    }
};
