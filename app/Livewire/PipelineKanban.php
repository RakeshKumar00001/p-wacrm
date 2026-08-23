<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\LeadStage;
use App\Models\Lead;
use App\Services\MetaCapiService;

class PipelineKanban extends Component
{
    // ── Panel state ────────────────────────────────────────────────
    public bool   $showPanel    = false;
    public ?int   $selectedLeadId = null;
    public string $saveMessage  = '';

    // ── Contact fields ─────────────────────────────────────────────
    public string $editName    = '';
    public string $editPhone   = '';
    public string $editEmail   = '';
    public string $editCompany = '';
    public string $editCity    = '';
    public string $editNotes   = '';  // contact notes

    // ── Lead fields ────────────────────────────────────────────────
    public string $editSource        = '';
    public string $editLeadNotes     = '';
    public float  $editExpectedValue = 0;
    public int    $editLeadScore     = 0;
    public string $editUtmSource     = '';
    public string $editUtmMedium     = '';
    public string $editUtmCampaign   = '';

    // ──────────────────────────────────────────────────────────────
    public function openLead(int $leadId): void
    {
        $lead = Lead::with(['contact', 'stage'])->find($leadId);
        if (!$lead) return;

        $this->selectedLeadId    = $leadId;
        $this->saveMessage       = '';

        // Contact
        $this->editName    = $lead->contact->name    ?? '';
        $this->editPhone   = $lead->contact->phone   ?? '';
        $this->editEmail   = $lead->contact->email   ?? '';
        $this->editCompany = $lead->contact->company ?? '';
        $this->editCity    = $lead->contact->city    ?? '';
        $this->editNotes   = $lead->contact->notes   ?? '';

        // Lead
        $this->editSource        = $lead->source         ?? '';
        $this->editLeadNotes     = $lead->notes          ?? '';
        $this->editExpectedValue = (float) ($lead->expected_value ?? 0);
        $this->editLeadScore     = (int)   ($lead->lead_score     ?? 0);
        $this->editUtmSource     = $lead->utm_source     ?? '';
        $this->editUtmMedium     = $lead->utm_medium     ?? '';
        $this->editUtmCampaign   = $lead->utm_campaign   ?? '';

        $this->showPanel = true;
    }

    public function saveLead(): void
    {
        $this->validate([
            'editName'          => 'nullable|string|max:255',
            'editEmail'         => 'nullable|email|max:255',
            'editExpectedValue' => 'nullable|numeric|min:0',
            'editLeadScore'     => 'nullable|integer|min:0|max:100',
        ]);

        $lead = Lead::with('contact')->find($this->selectedLeadId);
        if (!$lead) return;

        // Save contact
        $lead->contact->update([
            'name'    => $this->editName    ?: $lead->contact->name,
            'email'   => $this->editEmail   ?: null,
            'company' => $this->editCompany ?: null,
            'city'    => $this->editCity    ?: null,
            'notes'   => $this->editNotes   ?: null,
        ]);

        // Save lead
        $lead->update([
            'source'         => $this->editSource,
            'notes'          => $this->editLeadNotes,
            'expected_value' => $this->editExpectedValue,
            'lead_score'     => $this->editLeadScore,
            'utm_source'     => $this->editUtmSource   ?: null,
            'utm_medium'     => $this->editUtmMedium   ?: null,
            'utm_campaign'   => $this->editUtmCampaign ?: null,
        ]);

        $lead->activities()->create([
            'business_id' => $lead->business_id,
            'type'        => 'lead_updated',
            'description' => 'Lead details updated from Pipeline Kanban.',
        ]);

        $this->saveMessage = 'Saved!';
    }

    public function closePanel(): void
    {
        $this->showPanel       = false;
        $this->selectedLeadId  = null;
        $this->saveMessage     = '';
    }

    public function startChatWithLead(?int $leadId = null)
    {
        $id = $leadId ?: $this->selectedLeadId;
        if (!$id) return;

        $lead = Lead::find($id);
        if (!$lead || !$lead->contact_id) return;

        $businessId = auth()->user()->business_id;
        $conversation = \App\Models\Conversation::firstOrCreate(
            ['business_id' => $businessId, 'contact_id' => $lead->contact_id],
            [
                'status' => 'open',
                'unread_count' => 0,
            ]
        );

        return redirect()->to('/inbox?conversation_id=' . $conversation->id);
    }

    // ── Drag & drop stage update ───────────────────────────────────
    public function updateLeadStage($leadId, $newStageId, MetaCapiService $capiService)
    {
        $lead = Lead::find($leadId);
        if (!$lead) return;

        $lead->stage_id = $newStageId;
        $lead->save();

        \App\Services\DripCampaignService::handleStageChange($lead, $newStageId);

        $newStage = LeadStage::find($newStageId);

        if ($newStage && $newStage->mapped_meta_event) {
            $lead->activities()->create([
                'business_id' => $lead->business_id,
                'user_id'     => 1,
                'type'        => 'stage_change',
                'description' => "Kanban Drag: Lead stage changed to {$newStage->name}. Triggering CAPI Event: {$newStage->mapped_meta_event}",
            ]);

            $capiService->sendEvent($lead, $newStage->mapped_meta_event);
        }
    }

    public function render()
    {
        $business = auth()->user()->business;
        if (!$business) {
            return abort(403, 'No active business associated.');
        }

        $stages       = LeadStage::where('business_id', $business->id)->orderBy('order_index')->get();
        $leadsGrouped = Lead::where('business_id', $business->id)->with(['contact', 'assignedUser', 'stage'])->get()->groupBy('stage_id');

        $currencySymbol = match($business?->currency) {
            'INR'   => '₹',
            'USD'   => '$',
            'EUR'   => '€',
            'GBP'   => '£',
            default => $business?->currency ?? '$',
        };

        return view('livewire.pipeline-kanban', [
            'stages'         => $stages,
            'leadsGrouped'   => $leadsGrouped,
            'currencySymbol' => $currencySymbol,
        ]);
    }
}
