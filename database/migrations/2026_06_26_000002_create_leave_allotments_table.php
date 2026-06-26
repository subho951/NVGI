<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leave_allotments')) {
            return;
        }

        Schema::create('leave_allotments', function (Blueprint $table) {
            $table->id();
            $table->date('leave_tenure_from')->index();
            $table->date('leave_tenure_to')->index();
            $table->unsignedBigInteger('leave_type_id')->index();
            $table->decimal('front_desk_leave_count', 8, 2)->default(0);
            $table->decimal('group_d_leave_count', 8, 2)->default(0);
            $table->decimal('tsa_teacher_leave_count', 8, 2)->default(0);
            $table->decimal('vhs_teacher_leave_count', 8, 2)->default(0);
            $table->boolean('is_carry_forward')->default(false);
            $table->tinyInteger('status')->default(1)->index();
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->unique(
                ['leave_type_id', 'leave_tenure_from', 'leave_tenure_to'],
                'leave_allotment_type_tenure_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_allotments');
    }
};
