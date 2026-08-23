<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            // Personal / Identity
            $table->string('designation')->nullable()->after('company'); // Job title
            $table->date('birthday')->nullable()->after('designation');
            $table->string('whatsapp_number')->nullable()->after('birthday'); // separate WA if different

            // Location
            $table->string('city')->nullable()->after('whatsapp_number');
            $table->string('state')->nullable()->after('city');
            $table->string('country')->nullable()->after('state');
            $table->text('address')->nullable()->after('country');

            // Online Presence
            $table->string('website')->nullable()->after('address');
            $table->string('linkedin_url')->nullable()->after('website');
            $table->string('instagram_handle')->nullable()->after('linkedin_url');

            // Sales-specific
            $table->text('notes')->nullable()->after('instagram_handle');
            $table->string('status')->default('active')->after('notes'); // active, inactive, blocked
            $table->boolean('do_not_disturb')->default(false)->after('status');
            $table->timestamp('last_contacted_at')->nullable()->after('do_not_disturb');
            $table->json('custom_fields')->nullable()->after('last_contacted_at');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn([
                'designation', 'birthday', 'whatsapp_number',
                'city', 'state', 'country', 'address',
                'website', 'linkedin_url', 'instagram_handle',
                'notes', 'status', 'do_not_disturb',
                'last_contacted_at', 'custom_fields',
            ]);
        });
    }
};
