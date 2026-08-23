<?php

namespace App\Services;

use App\Models\Business;
use App\Models\CapiEvent;
use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaCapiService
{
    public function sendEvent(Lead $lead, string $eventName, array $customData = [])
    {
        $business = $lead->business;

        if (!$business->meta_pixel_id || !$business->capi_token) {
            Log::warning("CAPI not configured for business {$business->id}");
            return null;
        }

        // Generate a unique event ID for deduplication
        $eventId = 'event_' . $lead->id . '_' . $eventName . '_' . time();

        $userData = [
            'ph' => [hash('sha256', ltrim($lead->contact->phone, '+'))],
        ];

        if ($lead->contact->email) {
            $userData['em'] = [hash('sha256', strtolower(trim($lead->contact->email)))];
        }

        if ($lead->fbc) {
            $userData['fbc'] = $lead->fbc;
        }

        if ($lead->fbp) {
            $userData['fbp'] = $lead->fbp;
        }

        // ctwa_clid: Click-to-WhatsApp click ID — the primary signal Meta uses
        // to attribute this conversion back to the CTWA ad click. Must go in
        // user_data (not custom_data) for proper deduplication and attribution.
        if ($lead->ctwa_clid) {
            $userData['ctwa_clid'] = $lead->ctwa_clid;
        }

        $customDataPayload = array_merge([
            'lead_id'    => $lead->id,
            'lead_score' => $lead->lead_score,
        ], $customData);

        // Include referral headline for visibility in Meta Events Manager
        if ($lead->referral_headline) {
            $customDataPayload['referral_headline'] = $lead->referral_headline;
        }

        $eventData = [
            'data' => [
                [
                    'event_name'    => $eventName,
                    'event_time'    => time(),
                    'action_source' => 'system_generated',
                    'user_data'     => $userData,
                    'custom_data'   => $customDataPayload,
                    'event_id'      => $eventId,
                ]
            ],
            // 'test_event_code' => 'TESTXXXX', // Can be configured later
        ];

        // Store the event intent in the database
        $capiEvent = CapiEvent::create([
            'business_id' => $business->id,
            'lead_id' => $lead->id,
            'event_name' => $eventName,
            'event_id' => $eventId,
            'status' => 'pending',
            'request_data' => $eventData,
        ]);

        try {
            $response = Http::withToken($business->capi_token)
                ->post("https://graph.facebook.com/v19.0/{$business->meta_pixel_id}/events", $eventData);

            if ($response->successful()) {
                $capiEvent->update([
                    'status' => 'success',
                    'response_data' => $response->json(),
                ]);
            } else {
                $capiEvent->update([
                    'status' => 'failed',
                    'response_data' => $response->json(),
                    'error_message' => $response->body(),
                ]);
            }

            return $response->json();
        } catch (\Exception $e) {
            $capiEvent->update([
                'status' => 'error',
                'error_message' => $e->getMessage(),
            ]);
            Log::error("Meta CAPI Exception: " . $e->getMessage());
            return false;
        }
    }
}
