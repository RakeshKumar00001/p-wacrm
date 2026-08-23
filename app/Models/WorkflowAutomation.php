<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowAutomation extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'title',
        'trigger_type',
        'trigger_summary',
        'nodes',
        'connections',
        'status',
        'executed_count',
        'conversion_rate',
    ];

    protected $casts = [
        'nodes' => 'array',
        'connections' => 'array',
        'executed_count' => 'integer',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
