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
        Schema::create('branch_leads', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('upload_id');
            $table->bigInteger('master_lead_id');
            $table->bigInteger('lead_sl_no');
            $table->bigInteger('branch_id');
            $table->bigInteger('campaign_type_id');
            $table->bigInteger('campaign_id');
            $table->bigInteger('assigned_telecaller_id');
            $table->bigInteger('parent_status_id');
            $table->bigInteger('child_status_id');
            $table->string('next_followup_date');
            $table->string('next_followup_time');
            $table->tinyInteger('status')->default(1);
            $table->integer('created_by')->default(1);
            $table->integer('updated_by')->default(1);
            $table->softDeletes();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // Auto-updates on change
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_leads');
    }
};
