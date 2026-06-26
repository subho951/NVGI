<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('salary_heads')) {
            return;
        }

        Schema::create('salary_heads', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->index();
            $table->string('name')->unique();
            $table->text('short_description')->nullable();
            $table->string('calculation_type', 20)->index();
            $table->string('calculation_base', 80)->index();
            $table->decimal('calculation_amount', 14, 2)->default(0);
            $table->tinyInteger('status')->default(1)->index();
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_heads');
    }
};
