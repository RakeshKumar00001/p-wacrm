<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\DripCampaign;
use App\Models\DripCampaignSchedule;
use Illuminate\Support\Facades\Log;

class DripCampaignService
{
    /**
     * Trigger the drip campaigns for a lead when its stage changes.
     */
    public static function handleStageChange(Lead $lead, $newStageId)
    {
        // 1. Cancel all pending drip campaign schedules for this lead
        DripCampaignSchedule::where('lead_id', $lead->id)
            ->where('status', 'pending')
            ->delete();

        // 2. Find active drip campaigns triggered by the new stage
        $campaigns = DripCampaign::with('steps')
            ->where('business_id', $lead->business_id)
            ->where('trigger_stage_id', $newStageId)
            ->where('status', 'active')
            ->get();

        foreach ($campaigns as $campaign) {
            foreach ($campaign->steps as $step) {
                DripCampaignSchedule::create([
                    'drip_campaign_id'      => $campaign->id,
                    'drip_campaign_step_id' => $step->id,
                    'lead_id'               => $lead->id,
                    'status'                => 'pending',
                    'send_at'               => now()->addDays($step->delay_days),
                ]);
            }
        }
    }
}
