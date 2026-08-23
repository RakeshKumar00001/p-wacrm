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
        Schema::create('workflow_automations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('trigger_type')->default('keyword'); // keyword, stage_change, after_hours, lead_created
            $table->string('trigger_summary')->nullable();
            $table->json('nodes')->nullable();
            $table->json('connections')->nullable();
            $table->string('status')->default('ACTIVE'); // ACTIVE, PAUSED
            $table->integer('executed_count')->default(0);
            $table->string('conversion_rate')->default('0%');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_automations');
    }
};
