<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Business;
use Illuminate\Support\Facades\Http;

class WhatsAppConfig extends Component
{
    public $businessId;
    public $wabaId;
    public $phoneNumberId;
    public $whatsappAccessToken;
    public $webhookVerifyToken;
    public $webhookUrl;

    public $testPhone;
    public $testStatusMessage = null;
    public $testStatusType = 'info';

    public function mount()
    {
        $business = auth()->user()->business;
        if ($business) {
            $this->businessId = $business->id;
            $this->wabaId = $business->waba_id;
            $this->phoneNumberId = $business->phone_number_id;
            $this->whatsappAccessToken = $business->whatsapp_access_token;
            $this->webhookVerifyToken = env('META_VERIFY_TOKEN', 'wacrm_secret_verify_token_2026');
        }
        
        $this->webhookUrl = url('/api/whatsapp/webhook');
    }

    public function saveSettings()
    {
        $this->validate([
            'wabaId' => 'nullable|string',
            'phoneNumberId' => 'nullable|string',
            'whatsappAccessToken' => 'nullable|string',
        ]);

        try {
            $business = auth()->user()->business;
            if ($business) {
                $business->waba_id = trim($this->wabaId);
                $business->phone_number_id = trim($this->phoneNumberId);
                $business->whatsapp_access_token = trim($this->whatsappAccessToken);
                $business->save();
                
                $this->testStatusMessage = 'WhatsApp API settings saved successfully!';
                $this->testStatusType = 'success';
            } else {
                $this->testStatusMessage = 'No associated business found for your user account.';
                $this->testStatusType = 'error';
            }
        } catch (\Exception $e) {
            $this->testStatusMessage = 'Failed to save settings: ' . $e->getMessage();
            $this->testStatusType = 'error';
        }
    }

    public function testConnection()
    {
        $cleanPhoneId = trim($this->phoneNumberId);
        $cleanToken = trim(preg_replace('/\s+/', '', $this->whatsappAccessToken ?? ''));

        if (!$cleanPhoneId || !$cleanToken) {
            $this->testStatusMessage = 'Please enter Phone Number ID and Access Token.';
            $this->testStatusType = 'error';
            return;
        }

        try {
            // First attempt: Get Phone Number details
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $cleanToken,
            ])->get("https://graph.facebook.com/v20.0/{$cleanPhoneId}");

            if ($response->successful()) {
                $data = $response->json();
                $displayNumber = $data['display_phone_number'] ?? ($data['verified_name'] ?? 'Verified');
                $this->testStatusMessage = "Connection Successful! Phone Number: {$displayNumber}";
                $this->testStatusType = 'success';
                return;
            }

            // Fallback: Test token directly via /me
            $meResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $cleanToken,
            ])->get("https://graph.facebook.com/v20.0/me");

            $errorMsg = $response->json('error.message', 'Unknown Meta API Error');
            $errorCode = $response->json('error.code');
            $errorSubcode = $response->json('error.error_subcode');

            if ($meResponse->successful()) {
                $this->testStatusMessage = "Token is VALID, but Phone Number ID ('{$cleanPhoneId}') is incorrect or does not belong to this token. Meta error: {$errorMsg}";
            } else {
                $meErrorMsg = $meResponse->json('error.message', $errorMsg);
                $this->testStatusMessage = "Connection Failed: Access Token is invalid or expired. Meta response: {$meErrorMsg} (Code: {$errorCode})";
            }
            $this->testStatusType = 'error';
        } catch (\Exception $e) {
            $this->testStatusMessage = "Connection Error: " . $e->getMessage();
            $this->testStatusType = 'error';
        }
    }

    public function render()
    {
        return view('livewire.whatsapp-config');
    }
}
