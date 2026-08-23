<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AiSalesService
{
    public function analyzeAndQualify(Conversation $conversation, Lead $lead)
    {
        $business = $conversation->business;
        
        if (!$business->ai_api_key) {
            return false;
        }

        // Fetch recent messages
        if ($business->ai_read_previous_chats) {
            $conversationIds = Conversation::where('contact_id', $conversation->contact_id)
                ->where('business_id', $business->id)
                ->pluck('id');

            $messages = \App\Models\Message::whereIn('conversation_id', $conversationIds)
                ->orderBy('created_at', 'asc')
                ->take(100)
                ->get();
        } else {
            $messages = $conversation->messages()->orderBy('created_at', 'asc')->take(50)->get();
        }
        
        $chatHistory = "";
        foreach ($messages as $msg) {
            if ($msg->sender_type === 'contact') {
                $sender = 'Customer';
            } elseif ($msg->sender_type === 'ai') {
                $sender = 'AI Agent';
            } elseif ($msg->sender_type === 'agent') {
                $sender = 'Human Agent (Handover Resolved)';
            } elseif ($msg->sender_type === 'system') {
                if (str_contains(strtolower($msg->content), 're-enabled') || str_contains(strtolower($msg->content), 'reactivated')) {
                    $chatHistory .= "[System Note: AI was re-enabled by Human Agent. Previous human handovers are COMPLETED and RESOLVED.]\n";
                }
                continue;
            } else {
                continue;
            }
            $chatHistory .= "{$sender}: {$msg->content}\n";
        }

        $timezone = $business->timezone ?? 'UTC';
        $localHour = now()->timezone($timezone)->hour;
        $timeGreeting = 'Hello';
        if ($localHour >= 5 && $localHour < 12) {
            $timeGreeting = 'Good morning';
        } elseif ($localHour >= 12 && $localHour < 17) {
            $timeGreeting = 'Good afternoon';
        } elseif ($localHour >= 17 && $localHour < 21) {
            $timeGreeting = 'Good evening';
        }

        $contactName = $conversation->contact?->name ?? 'Customer';

        $systemPrompt = $business->ai_system_prompt ?? "You are a sales qualification AI. Extract structured data and communicate like a helpful human sales consultant.";
        
        $prompt = "
        Analyze the following WhatsApp conversation, qualify the lead, and write the next response to the customer.

        Context & Rules:
        - Time-of-Day Greeting Context: {$timeGreeting}
        - Customer Name: {$contactName}

        CRITICAL RESPONSE INSTRUCTIONS (for `next_reply`):
        1. LANGUAGE MATCHING: Carefully examine the customer's previous messages in the conversation history. Detect the customer's exact language, script, and dialect (e.g. Hinglish in Roman script like 'bhai deal ka kya hua', pure Hindi in Devanagari, English, Spanish, etc.). You MUST write your reply in that EXACT same language, script, and vocabulary.
        2. CONTEXTUAL RE-ENGAGEMENT: If the customer is replying after a re-engagement nudge or after a pause, reference what was previously discussed in the last chats ('what's going on with their product interest or deal').
        3. SOFT LEAD RE-CONVERSION: Slowly and warmly engage with the customer to guide them back into the business pipeline (clarifying their requirements, budget, or next steps) without sounding pushy or aggressive. Act like an empathetic human sales consultant.

        Respond ONLY in raw JSON format with the following keys:
        - contact_name (string or null: customer's real full name if shared in chat, e.g. 'Rakesh Kumar')
        - contact_email (string or null: customer's email address if shared in chat, e.g. 'user@example.com')
        - contact_company (string or null: customer's company name if shared in chat)
        - contact_designation (string or null: customer's job title or role if shared in chat)
        - contact_city (string or null: customer's city if shared in chat)
        - contact_state (string or null: customer's state if shared in chat)
        - contact_country (string or null: customer's country if shared in chat)
        - req_product (string or null: product/service customer expressed interest in)
        - req_budget (string or null: customer's budget)
        - req_timeline (string or null: customer's urgency/timeline)
        - expected_value (number or null: estimated deal value if mentioned)
        - lead_score (integer 0-100)
        - recommended_stage (string from: 'New Lead', 'Qualified', 'Quotation Sent')
        - handoff_required (boolean: true ONLY IF the customer's LATEST message asks to speak to a human agent or expresses anger right now. Do NOT mark true if a human agent has already responded to past requests or if AI was re-enabled.)
        - next_reply (string: your warm, contextual, language-matched response to the customer)

        Conversation:
        {$chatHistory}
        ";

        $result = null;

        try {
            $provider = $business->ai_provider ?? 'openai';
            $model = $business->ai_model;

            if ($provider === 'openai') {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $business->ai_api_key,
                    'Content-Type' => 'application/json',
                ])->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model ?? 'gpt-4o',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'response_format' => ['type' => 'json_object']
                ]);

                if ($response->successful()) {
                    $result = json_decode($response->json('choices')[0]['message']['content'], true);
                } else {
                    Log::error("OpenAI qualification error: " . $response->body());
                }
            } elseif ($provider === 'gemini') {
                $modelName = $model ?? 'gemini-1.5-flash';
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . $business->ai_api_key;

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post($url, [
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => $systemPrompt]
                        ]
                    ],
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json'
                    ]
                ]);

                if ($response->successful()) {
                    $content = $response->json('candidates.0.content.parts.0.text') ?? '{}';
                    $result = json_decode($content, true);
                } else {
                    Log::error("Gemini qualification error: " . $response->body());
                }
            } elseif ($provider === 'deepseek') {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $business->ai_api_key,
                    'Content-Type' => 'application/json',
                ])->post('https://api.deepseek.com/chat/completions', [
                    'model' => $model ?? 'deepseek-chat',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'response_format' => ['type' => 'json_object']
                ]);

                if ($response->successful()) {
                    $result = json_decode($response->json('choices')[0]['message']['content'], true);
                } else {
                    Log::error("DeepSeek qualification error: " . $response->body());
                }
            }

            if ($result) {
                // Safeguard: Check customer's LATEST message before allowing AI disable
                if (!empty($result['handoff_required'])) {
                    $latestContactMsg = $conversation->messages()
                        ->where('sender_type', 'contact')
                        ->latest()
                        ->first();

                    $latestText = strtolower(trim($latestContactMsg?->content ?? ''));
                    $handoffKeywords = [
                        'talk to agent', 'human agent', 'talk to human', 'speak to agent', 
                        'speak with human', 'representative', 'connect me to human', 
                        'agent please', 'human please', 'handover', 'talk to a human', 
                        'speak to a human', 'real person', 'human support', 'connect to agent',
                        'support team', 'transfer to agent', 'person please', 'live agent',
                        'human', 'agent'
                    ];

                    $hasHandoffKeyword = false;
                    foreach ($handoffKeywords as $kw) {
                        if (str_contains($latestText, $kw)) {
                            $hasHandoffKeyword = true;
                            break;
                        }
                    }

                    // If latest customer message has NO explicit handoff request, override handoff_required to false!
                    if (!$hasHandoffKeyword) {
                        Log::info("AI Handoff Safeguard: Overriding LLM handoff_required to false because latest message ('{$latestText}') contains no handoff request.");
                        $result['handoff_required'] = false;
                    }
                }

                $this->applyAiResults($conversation, $lead, $result);
                return $result;
            }
        } catch (\Exception $e) {
            Log::error("AI Qualification Error: " . $e->getMessage());
        }

        return false;
    }

    protected function applyAiResults(Conversation $conversation, Lead $lead, array $result)
    {
        // 1. Auto-fill Contact identity & location details shared by user
        $contact = $conversation->contact;
        if ($contact) {
            $updatedContact = false;

            if (!empty($result['contact_name'])) {
                $newName = trim($result['contact_name']);
                if (empty($contact->name) || str_contains(strtolower($contact->name), 'guest') || str_contains(strtolower($contact->name), 'unknown') || strlen($contact->name) < 3 || strtolower($contact->name) !== strtolower($newName)) {
                    $contact->name = $newName;
                    $updatedContact = true;
                }
            }

            if (!empty($result['contact_email']) && filter_var($result['contact_email'], FILTER_VALIDATE_EMAIL)) {
                $contact->email = trim($result['contact_email']);
                $updatedContact = true;
            }

            if (!empty($result['contact_company'])) {
                $contact->company = trim($result['contact_company']);
                $updatedContact = true;
            }

            if (!empty($result['contact_designation']) && Schema::hasColumn('contacts', 'designation')) {
                $contact->designation = trim($result['contact_designation']);
                $updatedContact = true;
            }

            if (!empty($result['contact_city']) && Schema::hasColumn('contacts', 'city')) {
                $contact->city = trim($result['contact_city']);
                $updatedContact = true;
            }

            if (!empty($result['contact_state']) && Schema::hasColumn('contacts', 'state')) {
                $contact->state = trim($result['contact_state']);
                $updatedContact = true;
            }

            if (!empty($result['contact_country']) && Schema::hasColumn('contacts', 'country')) {
                $contact->country = trim($result['contact_country']);
                $updatedContact = true;
            }

            if ($updatedContact) {
                $contact->save();
            }
        }

        // 2. Auto-fill Lead qualification & details
        if (isset($result['lead_score']) && is_numeric($result['lead_score'])) {
            $lead->lead_score = (int) $result['lead_score'];
        }
        if (!empty($result['req_product'])) {
            $lead->req_product = trim($result['req_product']);
        }
        if (!empty($result['req_budget'])) {
            $lead->req_budget = trim($result['req_budget']);
        }
        if (!empty($result['req_timeline'])) {
            $lead->req_timeline = trim($result['req_timeline']);
        }
        if (isset($result['expected_value']) && is_numeric($result['expected_value']) && $result['expected_value'] > 0) {
            $lead->expected_value = (float) $result['expected_value'];
        }
        $lead->save();

        // Lead stage auto-updating
        if (!empty($result['recommended_stage'])) {
            $stage = \App\Models\LeadStage::where('business_id', $lead->business_id)
                ->where('name', $result['recommended_stage'])
                ->first();
            if ($stage && $lead->stage_id != $stage->id) {
                $lead->stage_id = $stage->id;
                $lead->save();

                // Add a system note in the conversation
                \App\Models\Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_type' => 'system',
                    'type' => 'text',
                    'content' => "AI updated Lead Stage to: {$stage->name}",
                ]);
            }
        }

        // Check if handoff is required
        if (isset($result['handoff_required']) && $result['handoff_required']) {
            Conversation::ensureAiColumnsExist();
            $conversation->ai_enabled = false;
            $conversation->ai_handover_at = now();
            $conversation->status = 'open';

            $defaultResumeMins = (int)($conversation->business->ai_auto_resume_minutes ?? 0);
            if ($defaultResumeMins > 0) {
                $conversation->ai_auto_resume_at = now()->addMinutes($defaultResumeMins);
            }

            $conversation->save();

            // Locate appropriate user to notify
            $recipient = null;
            if ($conversation->assigned_user_id) {
                $recipient = \App\Models\User::find($conversation->assigned_user_id);
            } elseif ($lead->assigned_user_id) {
                $recipient = \App\Models\User::find($lead->assigned_user_id);
            } else {
                $recipient = \App\Models\User::where('business_id', $conversation->business_id)
                    ->whereIn('role', ['owner', 'manager', 'super_admin'])
                    ->first() ?? \App\Models\User::where('business_id', $conversation->business_id)->first();
            }

            // Create activity log
            $lead->activities()->create([
                'business_id' => $lead->business_id,
                'user_id' => null,
                'type' => 'handoff_request',
                'description' => "AI Agent requested human handoff for conversation with " . ($conversation->contact->name ?? $conversation->contact->phone) . ". Handed over to: " . ($recipient ? $recipient->name : 'Unassigned')
            ]);

            // Dispatch Email Notification
            if ($recipient && $recipient->email) {
                try {
                    \Illuminate\Support\Facades\Mail::to($recipient->email)->send(
                        new \App\Mail\HandoffNotificationMail($conversation, $recipient)
                    );
                } catch (\Exception $e) {
                    Log::error("Failed to send handoff email: " . $e->getMessage());
                }
            }
            
            // Add system note
            \App\Models\Message::create([
                'conversation_id' => $conversation->id,
                'sender_type' => 'system',
                'type' => 'text',
                'content' => "AI Agent disabled: Human handoff requested. Assigned to: " . ($recipient ? $recipient->name : 'Unassigned'),
            ]);
        }

        // Auto-reply logic: Create message and dispatch job to send via WhatsApp
        if ($conversation->ai_enabled && !empty($result['next_reply'])) {
            $aiMessage = \App\Models\Message::create([
                'conversation_id' => $conversation->id,
                'sender_type' => 'ai',
                'type' => 'text',
                'content' => $result['next_reply'],
                'status' => 'pending'
            ]);

            \App\Jobs\SendWhatsAppMessageJob::dispatchSync($aiMessage);
        }
    }
}
