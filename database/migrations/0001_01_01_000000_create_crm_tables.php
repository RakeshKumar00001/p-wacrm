<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Businesses (Multi-Tenancy)
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('waba_id')->nullable();
            $table->string('phone_number_id')->nullable();
            $table->text('whatsapp_access_token')->nullable();
            $table->string('meta_pixel_id')->nullable();
            $table->text('capi_token')->nullable();
            $table->string('ai_provider')->default('openai');
            $table->text('ai_api_key')->nullable();
            $table->string('ai_model')->nullable();
            $table->text('ai_system_prompt')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('currency')->default('INR');
            $table->timestamps();
        });

        // 2. Users & Teams
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('sales_agent'); // super_admin, owner, manager, agent
            $table->timestamps();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('team_user', function (Blueprint $table) {
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['team_id', 'user_id']);
        });

        // 3. Contacts
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('phone');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('company')->nullable();
            $table->unsignedBigInteger('merged_into_id')->nullable();
            $table->timestamps();
            
            $table->unique(['business_id', 'phone']);
        });

        // 4. Lead Stages
        Schema::create('lead_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., New Lead, Qualified, Won
            $table->integer('order_index')->default(0);
            $table->string('color')->default('#CCCCCC');
            $table->string('mapped_meta_event')->nullable(); // e.g., Lead, Purchase
            $table->timestamps();
        });

        // 5. Leads
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained('lead_stages')->cascadeOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            
            // AI Qualification fields
            $table->integer('lead_score')->default(0);
            $table->string('req_product')->nullable();
            $table->string('req_budget')->nullable();
            $table->string('req_timeline')->nullable();
            $table->decimal('expected_value', 15, 2)->nullable();
            $table->decimal('final_value', 15, 2)->nullable();

            // Attribution fields
            $table->string('fb_lead_id')->nullable();
            $table->string('source')->nullable();
            $table->string('campaign_id')->nullable();
            $table->string('campaign_name')->nullable();
            $table->string('adset_id')->nullable();
            $table->string('adset_name')->nullable();
            $table->string('ad_id')->nullable();
            $table->string('ad_name')->nullable();
            $table->string('fbc')->nullable();
            $table->string('fbp')->nullable();
            
            $table->timestamp('conversion_date')->nullable();
            $table->timestamps();
        });

        // 6. Conversations
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('open'); // open, pending, closed
            $table->boolean('ai_enabled')->default(true);
            $table->integer('unread_count')->default(0);
            $table->timestamps();
        });

        // 7. Messages
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->string('sender_type'); // contact, agent, ai, system
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('type')->default('text'); // text, image, document, template
            $table->text('content')->nullable();
            $table->string('status')->default('sent'); // sent, delivered, read, failed
            $table->string('meta_message_id')->nullable()->unique();
            $table->timestamps();
        });

        // 8. CAPI Events Log
        Schema::create('capi_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->string('event_name');
            $table->string('event_id')->unique();
            $table->string('status')->default('pending');
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();
        });
        
        // 9. Activity Timeline
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type'); // stage_change, note, call, meeting
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
        Schema::dropIfExists('capi_events');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('lead_stages');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('users');
        Schema::dropIfExists('businesses');
    }
};
