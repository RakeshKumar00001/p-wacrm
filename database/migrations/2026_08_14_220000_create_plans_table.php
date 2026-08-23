<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // starter, growth, enterprise
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->integer('trial_days')->default(7);
            $table->json('features'); // {"capi": false, "ai_agent": true, "webhooks": false, "max_agents": 3}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
