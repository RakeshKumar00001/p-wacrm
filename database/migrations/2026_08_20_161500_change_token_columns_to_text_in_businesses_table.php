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
        Schema::table('businesses', function (Blueprint $table) {
            $table->text('whatsapp_access_token')->nullable()->change();
            $table->text('capi_token')->nullable()->change();
            $table->text('ai_api_key')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('whatsapp_access_token')->nullable()->change();
            $table->string('capi_token')->nullable()->change();
            $table->string('ai_api_key')->nullable()->change();
        });
    }
};
