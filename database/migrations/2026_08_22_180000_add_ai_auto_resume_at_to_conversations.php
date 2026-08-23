<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('conversations')) {
            Schema::table('conversations', function (Blueprint $table) {
                if (!Schema::hasColumn('conversations', 'ai_auto_resume_at')) {
                    $table->timestamp('ai_auto_resume_at')->nullable()->after('ai_enabled');
                }
                if (!Schema::hasColumn('conversations', 'ai_handover_at')) {
                    $table->timestamp('ai_handover_at')->nullable()->after('ai_auto_resume_at');
                }
            });
        }

        if (Schema::hasTable('businesses')) {
            Schema::table('businesses', function (Blueprint $table) {
                if (!Schema::hasColumn('businesses', 'ai_auto_resume_minutes')) {
                    $table->integer('ai_auto_resume_minutes')->default(0)->after('auto_engage_enabled');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('conversations')) {
            Schema::table('conversations', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('conversations', 'ai_auto_resume_at')) $columns[] = 'ai_auto_resume_at';
                if (Schema::hasColumn('conversations', 'ai_handover_at')) $columns[] = 'ai_handover_at';
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('businesses')) {
            Schema::table('businesses', function (Blueprint $table) {
                if (Schema::hasColumn('businesses', 'ai_auto_resume_minutes')) {
                    $table->dropColumn('ai_auto_resume_minutes');
                }
            });
        }
    }
};
