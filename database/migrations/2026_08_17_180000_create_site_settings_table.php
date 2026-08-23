<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default values
        $defaults = [
            ['key' => 'site_name',           'value' => 'WACRM'],
            ['key' => 'site_tagline',         'value' => 'WhatsApp CRM & Sales Automation'],
            ['key' => 'meta_title',           'value' => 'WACRM — WhatsApp CRM & Sales Automation'],
            ['key' => 'meta_description',     'value' => 'Automate WhatsApp sales, qualify leads with AI, and close more deals.'],
            ['key' => 'logo_path',            'value' => null],
            ['key' => 'favicon_path',         'value' => null],
            ['key' => 'support_email',        'value' => 'support@wacrm.io'],
            ['key' => 'support_phone',        'value' => ''],
            ['key' => 'primary_color',        'value' => '#6366f1'],
            ['key' => 'copyright_text',       'value' => '© 2026 WACRM. All rights reserved.'],
            ['key' => 'google_analytics_id',  'value' => ''],
            ['key' => 'custom_head_scripts',  'value' => ''],
        ];

        foreach ($defaults as $row) {
            DB::table('site_settings')->insert(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
