<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Distributor extends Model
{
    protected $guarded = ['id'];

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }

    public function getBusinessCountAttribute(): int
    {
        return $this->businesses()->count();
    }

    public function getActiveBusinessCountAttribute(): int
    {
        return $this->businesses()->where('status', 'active')->count();
    }
}
