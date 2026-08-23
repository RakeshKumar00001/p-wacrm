<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add UTM + notes + platform fields to leads
        Schema::table('leads', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('source');
            $table->string('utm_source')->nullable()->after('notes');
            $table->string('utm_medium')->nullable()->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium');
            $table->string('utm_content')->nullable()->after('utm_campaign');
            $table->string('utm_term')->nullable()->after('utm_content');
            // Click-to-WhatsApp referral tracking
            $table->string('ctwa_clid')->nullable()->after('utm_term');
            $table->string('referral_headline')->nullable()->after('ctwa_clid');
        });

        // Add Meta Lead Ads webhook verify token + page_id to businesses
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('meta_page_id')->nullable()->after('capi_token');
            $table->string('meta_lead_ads_verify_token')->nullable()->after('meta_page_id');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'notes', 'utm_source', 'utm_medium', 'utm_campaign',
                'utm_content', 'utm_term', 'ctwa_clid', 'referral_headline',
            ]);
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['meta_page_id', 'meta_lead_ads_verify_token']);
        });
    }
};
