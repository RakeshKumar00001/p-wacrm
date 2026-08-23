<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Track when the last auto-engage nudge was sent for a conversation
        Schema::table('conversations', function (Blueprint $table) {
            $table->timestamp('auto_engaged_at')->nullable()->after('unread_count');
            $table->boolean('auto_engage_enabled')->default(true)->after('auto_engaged_at');
        });

        // Allow businesses to globally toggle auto-engage on/off
        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('auto_engage_enabled')->default(true)->after('ai_read_previous_chats');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['auto_engaged_at', 'auto_engage_enabled']);
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('auto_engage_enabled');
        });
    }
};
