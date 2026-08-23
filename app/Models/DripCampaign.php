<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DripCampaign extends Model
{
    protected $guarded = ['id'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function triggerStage(): BelongsTo
    {
        return $this->belongsTo(LeadStage::class, 'trigger_stage_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(DripCampaignStep::class)->orderBy('step_number');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DripCampaignSchedule::class);
    }
}
