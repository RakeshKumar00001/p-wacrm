<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Business;
use App\Models\LeadStage;
use Illuminate\Support\Facades\Http;

class MetaCapiConfig extends Component
{
    public $businessId;
    public $pixelId;
    public $capiToken;
    public $testEventCode;
    
    public $stages = [];
    public $mappings = [];
    
    public $testResponse = null;
    public $testError = null;

    public function mount()
    {
        // Multi-tenant: load current business
        $business = auth()->user()->business;
        $this->businessId = $business->id;
        $this->pixelId = $business->meta_pixel_id;
        $this->capiToken = $business->capi_token;
        
        // Load stages and their mappings
        $this->stages = LeadStage::where('business_id', $this->businessId)->orderBy('order_index')->get();
        foreach ($this->stages as $stage) {
            $this->mappings[$stage->id] = $stage->mapped_meta_event;
        }
    }

    public function saveConfiguration()
    {
        $business = auth()->user()->business;
        $business->update([
            'meta_pixel_id' => $this->pixelId,
            'capi_token' => $this->capiToken,
        ]);

        foreach ($this->mappings as $stageId => $eventName) {
            LeadStage::where('id', $stageId)->update([
                'mapped_meta_event' => $eventName ?: null
            ]);
        }

        session()->flash('message', 'CAPI Configuration Saved Successfully.');
    }

    public function sendTestEvent()
    {
        $this->testResponse = null;
        $this->testError = null;

        if (!$this->pixelId || !$this->capiToken) {
            $this->testError = 'Pixel ID and Access Token are required.';
            return;
        }

        $eventData = [
            'data' => [
                [
                    'event_name' => 'TestEvent',
                    'event_time' => time(),
                    'action_source' => 'system_generated',
                    'user_data' => [
                        'em' => [hash('sha256', 'test@example.com')]
                    ],
                ]
            ],
        ];

        if ($this->testEventCode) {
            $eventData['test_event_code'] = $this->testEventCode;
        }

        try {
            $response = Http::withToken($this->capiToken)
                ->post("https://graph.facebook.com/v19.0/{$this->pixelId}/events", $eventData);

            if ($response->successful()) {
                $this->testResponse = json_encode($response->json(), JSON_PRETTY_PRINT);
            } else {
                $this->testError = json_encode($response->json(), JSON_PRETTY_PRINT);
            }
        } catch (\Exception $e) {
            $this->testError = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.meta-capi-config');
    }
}
