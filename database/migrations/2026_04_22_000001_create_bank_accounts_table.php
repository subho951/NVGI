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
        if (!Schema::hasTable('bank_accounts')) {
            Schema::create('bank_accounts', function (Blueprint $table) {
                $table->id();
                $table->text('bank_name')->nullable();
                $table->text('bank_branch')->nullable();
                $table->text('account_no')->nullable();
                $table->text('ifsc_code')->nullable();
                $table->enum('account_type', ['CURRENT', 'SAVINGS'])->nullable();
                $table->tinyInteger('status')->default(1);
                $table->softDeletes();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
