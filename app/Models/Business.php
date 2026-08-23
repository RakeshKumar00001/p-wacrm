<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Business extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'auto_engage_enabled'   => 'boolean',
        'expires_at'            => 'datetime',
    ];

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function leadStages(): HasMany
    {
        return $this->hasMany(LeadStage::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function planDetails()
    {
        return Plan::where('slug', $this->plan)->first();
    }

    public function hasFeature(string $feature): bool
    {
        $details = $this->planDetails();
        if (!$details) {
            return false;
        }
        return (bool) ($details->features[$feature] ?? false);
    }

    public function getFeatureLimit(string $limitKey, int $default = 0): int
    {
        $details = $this->planDetails();
        if (!$details) {
            return $default;
        }
        return (int) ($details->features[$limitKey] ?? $default);
    }
}
