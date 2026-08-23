<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapiEvent extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
