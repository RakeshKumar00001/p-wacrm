<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add assigned_user_id to conversations table
        if (!Schema::hasColumn('conversations', 'assigned_user_id')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            });
        }

        // 2. Internal Conversation Notes (yellow team notes)
        if (!Schema::hasTable('conversation_notes')) {
            Schema::create('conversation_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('content');
                $table->timestamps();
            });
        }

        // 3. Contact Tags
        if (!Schema::hasTable('contact_tags')) {
            Schema::create('contact_tags', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
                $table->string('tag');
                $table->timestamps();
                $table->unique(['contact_id', 'tag']);
            });
        }

        // 4. Follow-up Reminders
        if (!Schema::hasTable('followup_reminders')) {
            Schema::create('followup_reminders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
                $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('remind_at');
                $table->string('note');
                $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('followup_reminders');
        Schema::dropIfExists('contact_tags');
        Schema::dropIfExists('conversation_notes');

        if (Schema::hasColumn('conversations', 'assigned_user_id')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->dropForeign(['assigned_user_id']);
                $table->dropColumn('assigned_user_id');
            });
        }
    }
};
