<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Business;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeveloperConfig extends Component
{
    public $businessId;
    public $apiKey;
    public $webhookUrl;
    public $webhookSecret;

    // Meta Lead Ads settings
    public $metaPageId;
    public $metaLeadAdsVerifyToken;

    // Status / Messages
    public $statusMessage = '';
    public $statusType = 'success';

    // Webhook Testing Properties
    public $testResult = null;
    public $testingInProgress = false;

    public function mount()
    {
        $business = auth()->user()->business;
        if ($business) {
            $this->businessId = $business->id;
            $this->apiKey = $business->api_key;
            $this->webhookUrl = $business->webhook_url;
            $this->webhookSecret = $business->webhook_secret;
            $this->metaPageId = $business->meta_page_id;
            $this->metaLeadAdsVerifyToken = $business->meta_lead_ads_verify_token;

            if (empty($this->apiKey)) {
                $this->regenerateApiKey();
            }
            if (empty($this->webhookSecret)) {
                $this->webhookSecret = 'whsec_' . bin2hex(random_bytes(16));
                $business->update(['webhook_secret' => $this->webhookSecret]);
            }
            if (empty($this->metaLeadAdsVerifyToken)) {
                $this->metaLeadAdsVerifyToken = 'mlav_' . bin2hex(random_bytes(12));
                $business->update(['meta_lead_ads_verify_token' => $this->metaLeadAdsVerifyToken]);
            }
        }
    }

    public function regenerateApiKey()
    {
        $business = auth()->user()->business;
        if ($business) {
            $this->apiKey = 'wacrm_' . bin2hex(random_bytes(16));
            $business->update(['api_key' => $this->apiKey]);

            $this->statusMessage = 'API Private Token successfully regenerated!';
            $this->statusType = 'success';
        }
    }

    public function regenerateLeadAdsVerifyToken()
    {
        $business = auth()->user()->business;
        if ($business) {
            $this->metaLeadAdsVerifyToken = 'mlav_' . bin2hex(random_bytes(12));
            $business->update(['meta_lead_ads_verify_token' => $this->metaLeadAdsVerifyToken]);

            $this->statusMessage = 'Meta Lead Ads verify token regenerated!';
            $this->statusType = 'success';
        }
    }

    public function saveSettings()
    {
        $this->validate([
            'webhookUrl' => 'nullable|url',
            'metaPageId' => 'nullable|string|max:100',
        ], [
            'webhookUrl.url' => 'The Webhook Destination must be a valid URL (starting with http:// or https://).',
        ]);

        $business = auth()->user()->business;
        if ($business) {
            $business->update([
                'webhook_url'  => $this->webhookUrl,
                'meta_page_id' => $this->metaPageId,
            ]);

            $this->statusMessage = 'Developer configurations saved successfully!';
            $this->statusType = 'success';
        } else {
            $this->statusMessage = 'Failed to find business record.';
            $this->statusType = 'error';
        }
    }

    public function sendTestWebhook()
    {
        if (empty($this->webhookUrl)) {
            $this->testResult = [
                'success' => false,
                'status'  => 'Error',
                'message' => 'Please configure and save a webhook destination URL first.'
            ];
            return;
        }

        $this->testingInProgress = true;
        $this->testResult = null;

        $payload = [
            'event'       => 'ping',
            'timestamp'   => now()->toIso8601String(),
            'business_id' => $this->businessId,
            'message'     => 'Hello! This is a test event from WACRM.',
            'data'        => ['ping' => true, 'version' => '1.0.0']
        ];

        $signature = hash_hmac('sha256', json_encode($payload), $this->webhookSecret);

        try {
            $response = Http::withHeaders([
                'X-Wacrm-Signature' => $signature,
                'Content-Type'      => 'application/json',
                'Accept'            => 'application/json',
            ])->timeout(5)->post($this->webhookUrl, $payload);

            $this->testResult = [
                'success' => $response->successful(),
                'status'  => $response->status() . ' ' . $response->reason(),
                'headers' => $response->headers(),
                'body'    => mb_strimwidth($response->body(), 0, 1000, '...')
            ];
        } catch (\Exception $e) {
            $this->testResult = [
                'success' => false,
                'status'  => 'Connection Failed',
                'message' => $e->getMessage()
            ];
        }

        $this->testingInProgress = false;
    }

    public function render()
    {
        return view('livewire.developer-config');
    }
}
