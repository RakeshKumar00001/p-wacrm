<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    protected $guarded = ['id'];
    protected $hidden = ['password'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
