<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Business;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Lead;
use App\Models\LeadStage;

class LeadsController extends Controller
{
    /**
     * Generic multi-platform lead intake endpoint.
     * Accepts leads from any platform: Google Ads, TikTok, website forms,
     * Zapier, Make, direct API, etc.
     *
     * POST /api/leads
     * Authorization: Bearer {api_key}
     */
    public function createFromApi(Request $request)
    {
        $token = $request->bearerToken();
        $business = Business::where('api_key', $token)->first();

        if (!$business) {
            return response()->json(['error' => 'Unauthorized. Invalid API Key.'], 401);
        }

        $validated = $request->validate([
            'phone'         => 'required|string',
            'name'          => 'nullable|string|max:255',
            'email'         => 'nullable|email|max:255',
            'expected_value'=> 'nullable|numeric',
            'source'        => 'nullable|string|max:100',
            'notes'         => 'nullable|string',
            // UTM attribution
            'utm_source'    => 'nullable|string|max:255',
            'utm_medium'    => 'nullable|string|max:255',
            'utm_campaign'  => 'nullable|string|max:255',
            'utm_content'   => 'nullable|string|max:255',
            'utm_term'      => 'nullable|string|max:255',
            // Campaign metadata (optional, for ad platforms)
            'campaign_id'   => 'nullable|string|max:255',
            'campaign_name' => 'nullable|string|max:255',
            'ad_id'         => 'nullable|string|max:255',
            'ad_name'       => 'nullable|string|max:255',
        ]);

        $contact = Contact::firstOrCreate(
            ['phone' => $validated['phone'], 'business_id' => $business->id],
            [
                'name'  => $validated['name'] ?? null,
                'email' => $validated['email'] ?? null,
            ]
        );

        // Update name/email if newly provided for an existing contact
        if (!$contact->wasRecentlyCreated) {
            $updates = [];
            if (!$contact->name && !empty($validated['name'])) {
                $updates['name'] = $validated['name'];
            }
            if (!$contact->email && !empty($validated['email'])) {
                $updates['email'] = $validated['email'];
            }
            if ($updates) {
                $contact->update($updates);
            }
        }

        $stage = LeadStage::where('business_id', $business->id)->orderBy('order_index')->first();

        $source = $validated['source'] ?? 'REST API';

        $lead = Lead::create([
            'business_id'   => $business->id,
            'contact_id'    => $contact->id,
            'stage_id'      => $stage ? $stage->id : 1,
            'expected_value'=> $validated['expected_value'] ?? 0,
            'source'        => $source,
            'notes'         => $validated['notes'] ?? null,
            'utm_source'    => $validated['utm_source'] ?? null,
            'utm_medium'    => $validated['utm_medium'] ?? null,
            'utm_campaign'  => $validated['utm_campaign'] ?? null,
            'utm_content'   => $validated['utm_content'] ?? null,
            'utm_term'      => $validated['utm_term'] ?? null,
            'campaign_id'   => $validated['campaign_id'] ?? null,
            'campaign_name' => $validated['campaign_name'] ?? null,
            'ad_id'         => $validated['ad_id'] ?? null,
            'ad_name'       => $validated['ad_name'] ?? null,
        ]);

        \App\Services\DripCampaignService::handleStageChange($lead, $lead->stage_id);

        Conversation::firstOrCreate(
            ['contact_id' => $contact->id, 'business_id' => $business->id],
            ['status' => 'open', 'ai_enabled' => true]
        );

        $lead->activities()->create([
            'business_id' => $business->id,
            'type'        => 'lead_created',
            'description' => "Lead created via {$source}" . ($validated['utm_campaign'] ? " (Campaign: {$validated['utm_campaign']})" : ''),
        ]);

        // Fire outbound webhook if configured
        if ($business->webhook_url) {
            $this->fireWebhook($business, 'lead.created', [
                'phone'         => $contact->phone,
                'contact_name'  => $contact->name,
                'source'        => $source,
                'stage'         => $stage ? $stage->name : 'New Lead',
                'expected_value'=> $lead->expected_value,
                'utm_source'    => $lead->utm_source,
                'utm_campaign'  => $lead->utm_campaign,
            ]);
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Lead created successfully',
            'lead_id'    => $lead->id,
            'contact_id' => $contact->id,
            'source'     => $source,
        ], 201);
    }

    /**
     * Meta Lead Ads Webhook — Verification (GET).
     * Meta sends a GET request to verify the endpoint.
     *
     * GET /api/webhooks/meta-lead-ads
     */
    public function verifyMetaLeadAdsWebhook(Request $request)
    {
        $mode      = $request->query('hub_mode');
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        // Find business by verify token
        $business = Business::where('meta_lead_ads_verify_token', $token)->first();

        if ($mode === 'subscribe' && $business) {
            Log::info("Meta Lead Ads webhook verified for business {$business->id}");
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Meta Lead Ads Webhook — Lead Received (POST).
     * Meta sends a POST when a user submits a lead form on Facebook/Instagram.
     *
     * POST /api/webhooks/meta-lead-ads
     */
    public function handleMetaLeadAdsWebhook(Request $request)
    {
        $payload = $request->all();

        // Meta Lead Ads sends: object = "page", entry[].changes[].value.leadgen_id + page_id
        if (($payload['object'] ?? '') !== 'page') {
            return response('OK', 200);
        }

        try {
            foreach ($payload['entry'] ?? [] as $entry) {
                foreach ($entry['changes'] ?? [] as $change) {
                    if (($change['field'] ?? '') !== 'leadgen') {
                        continue;
                    }

                    $value    = $change['value'];
                    $leadgenId = $value['leadgen_id'] ?? null;
                    $pageId    = $value['page_id'] ?? null;
                    $formId    = $value['form_id'] ?? null;
                    $adId      = $value['ad_id'] ?? null;
                    $adName    = $value['ad_name'] ?? null;
                    $campaignId   = $value['campaign_id'] ?? null;
                    $campaignName = $value['campaign_name'] ?? null;
                    $adsetId      = $value['adset_id'] ?? null;
                    $adsetName    = $value['adset_name'] ?? null;

                    if (!$leadgenId || !$pageId) continue;

                    // Find the business by page_id
                    $business = Business::where('meta_page_id', $pageId)->first();
                    if (!$business) {
                        Log::warning("Meta Lead Ads: No business found for page_id: {$pageId}");
                        continue;
                    }

                    // Fetch lead form field data from Meta Graph API
                    $leadData = $this->fetchMetaLeadData($leadgenId, $business->whatsapp_access_token);
                    if (!$leadData) {
                        Log::error("Meta Lead Ads: Failed to fetch leadgen data for {$leadgenId}");
                        continue;
                    }

                    // Parse fields from Meta lead form response
                    $fields = [];
                    foreach ($leadData['field_data'] ?? [] as $field) {
                        $fields[strtolower($field['name'])] = $field['values'][0] ?? null;
                    }

                    $phone = $fields['phone_number'] ?? $fields['phone'] ?? null;
                    $name  = trim(($fields['first_name'] ?? '') . ' ' . ($fields['last_name'] ?? '')) ?: ($fields['full_name'] ?? null);
                    $email = $fields['email'] ?? null;

                    if (!$phone) {
                        Log::warning("Meta Lead Ads: No phone in lead form {$leadgenId}, skipping.");
                        continue;
                    }

                    // Normalize phone
                    $phone = preg_replace('/[^0-9+]/', '', $phone);
                    if (!str_starts_with($phone, '+')) {
                        $phone = '+' . $phone;
                    }

                    $contact = Contact::firstOrCreate(
                        ['phone' => $phone, 'business_id' => $business->id],
                        ['name' => $name, 'email' => $email]
                    );

                    if (!$contact->wasRecentlyCreated) {
                        $updates = [];
                        if (!$contact->name && $name) $updates['name'] = $name;
                        if (!$contact->email && $email) $updates['email'] = $email;
                        if ($updates) $contact->update($updates);
                    }

                    $stage = LeadStage::where('business_id', $business->id)->orderBy('order_index')->first();

                    // Avoid duplicate leads from same form submission
                    $existingLead = Lead::where('business_id', $business->id)
                        ->where('fb_lead_id', $leadgenId)
                        ->first();

                    if ($existingLead) {
                        Log::info("Meta Lead Ads: Duplicate leadgen_id {$leadgenId}, skipping.");
                        continue;
                    }

                    $lead = Lead::create([
                        'business_id'   => $business->id,
                        'contact_id'    => $contact->id,
                        'stage_id'      => $stage ? $stage->id : 1,
                        'expected_value'=> 0,
                        'source'        => 'Meta Lead Ads',
                        'fb_lead_id'    => $leadgenId,
                        'campaign_id'   => $campaignId,
                        'campaign_name' => $campaignName,
                        'adset_id'      => $adsetId,
                        'adset_name'    => $adsetName,
                        'ad_id'         => $adId,
                        'ad_name'       => $adName,
                        'utm_source'    => 'facebook',
                        'utm_medium'    => 'paid_social',
                        'utm_campaign'  => $campaignName,
                    ]);

                    \App\Services\DripCampaignService::handleStageChange($lead, $lead->stage_id);

                    Conversation::firstOrCreate(
                        ['contact_id' => $contact->id, 'business_id' => $business->id],
                        ['status' => 'open', 'ai_enabled' => true]
                    );

                    $lead->activities()->create([
                        'business_id' => $business->id,
                        'type'        => 'lead_created',
                        'description' => "Lead received from Meta Lead Ads form. Ad: " . ($adName ?? 'N/A') . ", Campaign: " . ($campaignName ?? 'N/A'),
                    ]);

                    // Fire outbound webhook
                    if ($business->webhook_url) {
                        $this->fireWebhook($business, 'lead.created', [
                            'phone'        => $contact->phone,
                            'contact_name' => $contact->name,
                            'source'       => 'Meta Lead Ads',
                            'campaign'     => $campaignName,
                            'ad_name'      => $adName,
                        ]);
                    }

                    Log::info("Meta Lead Ads: Created lead #{$lead->id} for {$phone} from campaign: {$campaignName}");
                }
            }
        } catch (\Exception $e) {
            Log::error("Meta Lead Ads webhook error: " . $e->getMessage());
        }

        return response('EVENT_RECEIVED', 200);
    }

    /**
     * Fetch lead field data from Meta Graph API using the leadgen_id.
     */
    private function fetchMetaLeadData(string $leadgenId, ?string $accessToken): ?array
    {
        if (!$accessToken) return null;

        try {
            $response = Http::withToken($accessToken)
                ->get("https://graph.facebook.com/v19.0/{$leadgenId}", [
                    'fields' => 'field_data,created_time,ad_id,ad_name,form_id'
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("Meta Graph API error fetching leadgen {$leadgenId}: " . $response->body());
        } catch (\Exception $e) {
            Log::error("Meta Graph API exception: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Fire outbound webhook to the business's configured webhook URL.
     */
    private function fireWebhook(Business $business, string $event, array $data): void
    {
        try {
            $payload = [
                'event'     => $event,
                'timestamp' => now()->toIso8601String(),
                'data'      => $data,
            ];
            $signature = hash_hmac('sha256', json_encode($payload), $business->webhook_secret);

            Http::withHeaders([
                'X-Wacrm-Signature' => $signature,
                'Content-Type'      => 'application/json',
            ])->post($business->webhook_url, $payload);
        } catch (\Exception $e) {
            Log::error("Outbound webhook failed on {$event}: " . $e->getMessage());
        }
    }
}
