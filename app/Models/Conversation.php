<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'ai_enabled'          => 'boolean',
        'auto_engage_enabled' => 'boolean',
        'auto_engaged_at'     => 'datetime',
        'ai_auto_resume_at'   => 'datetime',
        'ai_handover_at'      => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ConversationNote::class);
    }

    public static function ensureAiColumnsExist()
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('conversations')) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('conversations', 'ai_auto_resume_at')) {
                try {
                    \Illuminate\Support\Facades\Schema::table('conversations', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->timestamp('ai_auto_resume_at')->nullable();
                        $table->timestamp('ai_handover_at')->nullable();
                    });
                } catch (\Throwable $e) {}
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('businesses')) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('businesses', 'ai_auto_resume_minutes')) {
                try {
                    \Illuminate\Support\Facades\Schema::table('businesses', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->integer('ai_auto_resume_minutes')->default(0);
                    });
                } catch (\Throwable $e) {}
            }
        }
    }
}
