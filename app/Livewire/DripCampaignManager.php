<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Business;
use App\Models\LeadStage;
use App\Models\DripCampaign;
use App\Models\DripCampaignStep;
use App\Models\DripCampaignSchedule;
use Illuminate\Support\Facades\Http;

class DripCampaignManager extends Component
{
    public $campaigns = [];
    public $availableTemplates = [];
    public $leadStages = [];

    // Drip Campaign Creation Form State
    public $campaignName = '';
    public $triggerStageId = null;

    // Selected campaign for viewing/adding steps
    public $selectedCampaignId = null;
    
    // Step creation form
    public $newStepTemplateName = '';
    public $newStepDelayDays = 1;

    // Toast/Alert
    public $statusMessage = null;
    public $statusType = 'info';

    public function mount()
    {
        $this->loadAvailableTemplates();
        $this->loadLeadStages();
        $this->loadCampaigns();

        if (count($this->campaigns) > 0) {
            $this->selectCampaign($this->campaigns[0]['id']);
        }
    }

    public function loadLeadStages()
    {
        $this->leadStages = LeadStage::where('business_id', auth()->user()->business_id)
            ->orderBy('order_index')
            ->get();
        if ($this->leadStages->isNotEmpty()) {
            $this->triggerStageId = $this->leadStages->first()->id;
        }
    }

    public function loadAvailableTemplates()
    {
        $business = auth()->user()->business;
        if ($business && $business->waba_id && $business->whatsapp_access_token) {
            try {
                $response = Http::withToken($business->whatsapp_access_token)
                    ->get("https://graph.facebook.com/v19.0/{$business->waba_id}/message_templates");
                if ($response->successful()) {
                    $this->availableTemplates = array_filter($response->json('data', []), function ($t) {
                        return ($t['status'] ?? '') === 'APPROVED';
                    });
                }
            } catch (\Exception $e) {}
        }

        if (empty($this->availableTemplates)) {
            $this->availableTemplates = [
                ['name' => 'lead_welcome_offer', 'category' => 'MARKETING'],
                ['name' => 'quotation_ready_link', 'category' => 'UTILITY'],
                ['name' => 'flash_sale_promo', 'category' => 'MARKETING'],
            ];
        }

        if (count($this->availableTemplates) > 0) {
            $this->newStepTemplateName = $this->availableTemplates[0]['name'];
        }
    }

    public function loadCampaigns()
    {
        $this->campaigns = DripCampaign::where('business_id', auth()->user()->business_id)
            ->with(['triggerStage', 'steps'])
            ->withCount([
                'schedules as pending_count' => fn($q) => $q->where('status', 'pending'),
                'schedules as sent_count' => fn($q) => $q->where('status', 'sent'),
                'schedules as failed_count' => fn($q) => $q->where('status', 'failed')
            ])
            ->get()
            ->toArray();
    }

    public function selectCampaign($campaignId)
    {
        $this->selectedCampaignId = $campaignId;
    }

    public function createCampaign()
    {
        $this->validate([
            'campaignName' => 'required|string|min:3',
            'triggerStageId' => 'required|exists:lead_stages,id'
        ]);

        $campaign = DripCampaign::create([
            'business_id' => auth()->user()->business_id,
            'name' => $this->campaignName,
            'trigger_stage_id' => $this->triggerStageId,
            'status' => 'draft'
        ]);

        $this->statusMessage = "Drip Campaign '{$this->campaignName}' created as Draft!";
        $this->statusType = 'success';

        $this->campaignName = '';
        $this->loadCampaigns();
        $this->selectCampaign($campaign->id);
    }

    public function addStep()
    {
        $this->validate([
            'newStepTemplateName' => 'required|string',
            'newStepDelayDays' => 'required|integer|min:0'
        ]);

        if (!$this->selectedCampaignId) {
            $this->statusMessage = "Please select a drip campaign first.";
            $this->statusType = 'error';
            return;
        }

        $campaign = DripCampaign::find($this->selectedCampaignId);
        if (!$campaign) return;

        $nextStepNumber = $campaign->steps()->count() + 1;

        DripCampaignStep::create([
            'drip_campaign_id' => $campaign->id,
            'step_number' => $nextStepNumber,
            'delay_days' => $this->newStepDelayDays,
            'template_name' => $this->newStepTemplateName
        ]);

        $this->statusMessage = "Step {$nextStepNumber} added successfully!";
        $this->statusType = 'success';

        $this->loadCampaigns();
    }

    public function deleteStep($stepId)
    {
        $step = DripCampaignStep::find($stepId);
        if ($step) {
            $campaignId = $step->drip_campaign_id;
            $step->delete();

            // Reorder remaining steps
            $remainingSteps = DripCampaignStep::where('drip_campaign_id', $campaignId)
                ->orderBy('step_number')
                ->get();
            
            foreach ($remainingSteps as $index => $rStep) {
                $rStep->update(['step_number' => $index + 1]);
            }

            $this->statusMessage = "Step deleted and sequence reordered!";
            $this->statusType = 'success';

            $this->loadCampaigns();
        }
    }

    public function toggleStatus($campaignId, $newStatus)
    {
        $campaign = DripCampaign::find($campaignId);
        if ($campaign && in_array($newStatus, ['active', 'paused', 'draft'])) {
            $campaign->update(['status' => $newStatus]);
            $this->statusMessage = "Campaign status updated to " . ucfirst($newStatus) . "!";
            $this->statusType = 'success';
            $this->loadCampaigns();
        }
    }

    public function deleteCampaign($campaignId)
    {
        $campaign = DripCampaign::find($campaignId);
        if ($campaign) {
            $campaign->delete();
            $this->statusMessage = "Drip Campaign deleted successfully!";
            $this->statusType = 'success';
            $this->selectedCampaignId = null;
            $this->loadCampaigns();
            if (count($this->campaigns) > 0) {
                $this->selectCampaign($this->campaigns[0]['id']);
            }
        }
    }

    public function render()
    {
        $selectedCampaign = $this->selectedCampaignId 
            ? collect($this->campaigns)->firstWhere('id', $this->selectedCampaignId) 
            : null;

        return view('livewire.drip-campaign-manager', [
            'selectedCampaign' => $selectedCampaign
        ]);
    }
}
