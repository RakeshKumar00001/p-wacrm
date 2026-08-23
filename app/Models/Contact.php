<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'birthday'          => 'date',
        'do_not_disturb'    => 'boolean',
        'last_contacted_at' => 'datetime',
        'custom_fields'     => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    // Helper: latest lead
    public function latestLead()
    {
        return $this->hasOne(Lead::class)->latestOfMany();
    }

    // Helper: tags as array
    public function getTagsArrayAttribute(): array
    {
        return array_filter(array_map('trim', explode(',', $this->tags ?? '')));
    }

    // Helper: initials for avatar
    public function getInitialsAttribute(): string
    {
        $name = $this->name ?? $this->phone ?? '?';
        $parts = explode(' ', trim($name));
        return strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
    }

    // Helper: avatar background color based on name hash
    public function getAvatarColorAttribute(): string
    {
        $colors = [
            'bg-indigo-500', 'bg-violet-500', 'bg-emerald-500',
            'bg-blue-500',   'bg-rose-500',   'bg-amber-500',
            'bg-cyan-500',   'bg-pink-500',   'bg-teal-500',
        ];
        return $colors[abs(crc32($this->name ?? $this->phone ?? '')) % count($colors)];
    }
}
