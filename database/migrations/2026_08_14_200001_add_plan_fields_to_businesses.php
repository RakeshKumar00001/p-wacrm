<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->foreignId('distributor_id')->nullable()->constrained('distributors')->nullOnDelete()->after('id');
            $table->string('status')->default('active')->after('currency');   // active, suspended, trial
            $table->string('plan')->default('starter')->after('status');      // starter, growth, enterprise
            $table->timestamp('expires_at')->nullable()->after('plan');
            $table->string('owner_email')->nullable()->after('name');
            $table->string('owner_phone')->nullable()->after('owner_email');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropForeign(['distributor_id']);
            $table->dropColumn(['distributor_id', 'status', 'plan', 'expires_at', 'owner_email', 'owner_phone']);
        });
    }
};
