<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Lead;
use App\Services\AiSalesService;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $expectedToken = env('META_VERIFY_TOKEN', 'wacrm_secret_verify_token_2026');

        if ($mode && $token) {
            if ($mode === 'subscribe' && $token === $expectedToken) {
                return response($challenge, 200);
            }
            return response('Forbidden', 403);
        }
    }

    public function handle(Request $request, AiSalesService $aiService)
    {
        $payload = $request->all();
        
        try {
            $entry = $payload['entry'][0] ?? null;
            if (!$entry) return response('OK', 200);

            $changes = $entry['changes'][0]['value'] ?? null;
            if (!$changes) return response('OK', 200);

            // Handle incoming messages
            if (isset($changes['messages'][0])) {
                $messageData = $changes['messages'][0];
                $contactData = $changes['contacts'][0] ?? null;
                $metadata = $changes['metadata'];

                $phoneNumberId = $metadata['phone_number_id'];
                
                // Identify Business
                $business = Business::where('phone_number_id', $phoneNumberId)->first();
                if (!$business) {
                    Log::error("Business not found for phone_number_id: $phoneNumberId");
                    return response('OK', 200);
                }

                $customerPhone = $messageData['from'];
                $customerName = $contactData['profile']['name'] ?? 'Unknown';

                // Find or Create Contact
                $contact = Contact::firstOrCreate(
                    ['business_id' => $business->id, 'phone' => $customerPhone],
                    ['name' => $customerName]
                );

                // Find or Create Conversation
                $conversation = Conversation::firstOrCreate(
                    ['business_id' => $business->id, 'contact_id' => $contact->id],
                    ['status' => 'open', 'ai_enabled' => true]
                );

                if ($conversation->wasRecentlyCreated && !$conversation->assigned_user_id) {
                    app(\App\Services\RoundRobinAssignmentService::class)->assignConversation($conversation);
                }

                // Create Message
                $message = Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_type' => 'contact',
                    'sender_id' => $contact->id,
                    'type' => $messageData['type'],
                    'content' => $messageData['text']['body'] ?? json_encode($messageData),
                    'meta_message_id' => $messageData['id'],
                    'status' => 'delivered'
                ]);

                // Ensure Lead exists
                $leadData = ['stage_id' => 1, 'source' => 'WhatsApp Inbound'];

                // CTWA (Click-to-WhatsApp) Attribution — capture referral from Meta ad click
                $referral = $messageData['referral'] ?? null;
                if ($referral) {
                    $leadData['source']           = 'Meta Ads (CTWA)';
                    $leadData['ctwa_clid']         = $referral['ctwa_clid'] ?? null;
                    $leadData['ad_id']             = $referral['source_id'] ?? null;
                    $leadData['utm_source']        = 'facebook';
                    $leadData['utm_medium']        = 'paid_social';
                    $leadData['referral_headline'] = $referral['headline'] ?? null;
                    Log::info("CTWA referral captured for {$customerPhone}: " . json_encode($referral));
                }

                $lead = Lead::firstOrCreate(
                    ['business_id' => $business->id, 'contact_id' => $contact->id],
                    $leadData
                );

                // Update CTWA fields even if lead existed (re-click from ad)
                if (!$lead->wasRecentlyCreated && $referral && !$lead->ctwa_clid) {
                    $lead->update([
                        'ctwa_clid'         => $referral['ctwa_clid'] ?? null,
                        'ad_id'             => $referral['source_id'] ?? null,
                        'source'            => 'Meta Ads (CTWA)',
                        'utm_source'        => 'facebook',
                        'utm_medium'        => 'paid_social',
                        'referral_headline' => $referral['headline'] ?? null,
                    ]);
                }

                // Ensure schema columns exist
                Conversation::ensureAiColumnsExist();

                // 1. Auto-Reactivate AI if timer expired
                if (!$conversation->ai_enabled && $conversation->ai_auto_resume_at && now()->isAfter($conversation->ai_auto_resume_at)) {
                    $conversation->update([
                        'ai_enabled' => true,
                        'ai_auto_resume_at' => null,
                    ]);

                    Message::create([
                        'conversation_id' => $conversation->id,
                        'sender_type'     => 'system',
                        'type'            => 'text',
                        'content'         => '🤖 AI Agent automatically reactivated based on scheduled timer.',
                    ]);
                }

                // 2. Keyword-based immediate Human Handoff check
                $msgBody = strtolower(trim($messageData['text']['body'] ?? ''));
                $handoffKeywords = ['talk to agent', 'human agent', 'talk to human', 'speak to agent', 'speak with human', 'representative', 'connect me to human', 'agent please', 'human please', 'handover', 'talk to a human', 'speak to a human', 'real person', 'human support'];
                $isExplicitHandoff = false;

                if (!empty($msgBody)) {
                    foreach ($handoffKeywords as $kw) {
                        if (str_contains($msgBody, $kw)) {
                            $isExplicitHandoff = true;
                            break;
                        }
                    }
                }

                if ($isExplicitHandoff && $conversation->ai_enabled) {
                    $defaultResumeMins = (int)($business->ai_auto_resume_minutes ?? 0);
                    $resumeAt = $defaultResumeMins > 0 ? now()->addMinutes($defaultResumeMins) : null;

                    $conversation->update([
                        'ai_enabled'        => false,
                        'ai_handover_at'    => now(),
                        'ai_auto_resume_at' => $resumeAt,
                        'status'            => 'open',
                    ]);

                    $recipient = $conversation->assignedUser ?? \App\Models\User::where('business_id', $business->id)->first();

                    Message::create([
                        'conversation_id' => $conversation->id,
                        'sender_type'     => 'system',
                        'type'            => 'text',
                        'content'         => "⚠️ AI Agent disabled: Customer requested human handoff." . ($resumeAt ? " (Auto-resume set for {$defaultResumeMins} mins)" : ""),
                    ]);

                    if ($recipient && $recipient->email) {
                        try {
                            \Illuminate\Support\Facades\Mail::to($recipient->email)->send(
                                new \App\Mail\HandoffNotificationMail($conversation, $recipient)
                            );
                        } catch (\Throwable $e) {}
                    }
                } elseif ($conversation->ai_enabled) {
                    // 3. Trigger AI if enabled
                    $aiService->analyzeAndQualify($conversation, $lead);
                }

            }

        } catch (\Exception $e) {
            Log::error("Webhook error: " . $e->getMessage());
        }

        return response('OK', 200);
    }
}
