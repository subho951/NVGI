<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employee_schedule_rosters')) {
            return;
        }

        Schema::create('employee_schedule_rosters', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id')->index();
            $table->text('employee_no')->nullable();
            $table->text('employee_name')->nullable();
            $table->string('category', 100)->index();
            $table->integer('unit_id')->nullable()->index();
            $table->text('unit_name')->nullable();
            $table->integer('branch_id')->index();
            $table->text('branch_name')->nullable();
            $table->date('roster_date')->index();
            $table->unsignedTinyInteger('roster_month')->index();
            $table->unsignedSmallInteger('roster_year')->index();
            $table->string('day_name', 20)->nullable();
            $table->string('in_time', 5)->nullable();
            $table->string('out_time', 5)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('generated_at')->nullable();
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->unique(['employee_id', 'category', 'branch_id', 'roster_date'], 'employee_schedule_roster_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_schedule_rosters');
    }
};
