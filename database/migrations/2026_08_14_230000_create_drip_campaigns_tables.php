<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drip_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->string('name');
            $table->foreignId('trigger_stage_id')->constrained('lead_stages')->onDelete('cascade');
            $table->string('status')->default('draft'); // draft, active, paused
            $table->timestamps();
        });

        Schema::create('drip_campaign_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drip_campaign_id')->constrained('drip_campaigns')->onDelete('cascade');
            $table->integer('step_number');
            $table->integer('delay_days')->default(0);
            $table->string('template_name');
            $table->timestamps();
        });

        Schema::create('drip_campaign_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drip_campaign_id')->constrained('drip_campaigns')->onDelete('cascade');
            $table->foreignId('drip_campaign_step_id')->constrained('drip_campaign_steps')->onDelete('cascade');
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, sent, failed
            $table->timestamp('send_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drip_campaign_schedules');
        Schema::dropIfExists('drip_campaign_steps');
        Schema::dropIfExists('drip_campaigns');
    }
};
