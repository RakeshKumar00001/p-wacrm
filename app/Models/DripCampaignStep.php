<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DripCampaignStep extends Model
{
    protected $guarded = ['id'];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(DripCampaign::class, 'drip_campaign_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DripCampaignSchedule::class, 'drip_campaign_step_id');
    }
}
