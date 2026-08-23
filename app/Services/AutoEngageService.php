<?php

namespace App\Services;

use App\Jobs\SendWhatsAppMessageJob;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AutoEngageService
{
    // How many hours before window expiry to trigger a nudge (2 hrs = send when 22+ hrs have passed)
    const TRIGGER_HOURS_BEFORE_EXPIRY = 2;

    // Cooldown: don't send another nudge within this many hours
    const NUDGE_COOLDOWN_HOURS = 4;

    // WhatsApp session window duration
    const SESSION_WINDOW_HOURS = 24;

    /**
     * Find all conversations that need a re-engagement nudge.
     */
    public function getEligibleConversations(): \Illuminate\Support\Collection
    {
        $windowOpenedAt   = now()->subHours(self::SESSION_WINDOW_HOURS - self::TRIGGER_HOURS_BEFORE_EXPIRY); // 22 hrs ago
        $windowExpiredAt  = now()->subHours(self::SESSION_WINDOW_HOURS); // 24 hrs ago — already expired, skip

        return Conversation::query()
            ->with(['business', 'contact.leads.stage', 'messages'])
            ->where('status', 'open')
            ->whereHas('business', fn($q) => $q->where('auto_engage_enabled', true)
                ->whereNotNull('phone_number_id')
                ->whereNotNull('whatsapp_access_token')
            )
            ->where(function ($q) {
                // Only engage conversations where per-conversation auto-engage is enabled (or not set)
                $q->where('auto_engage_enabled', true);
            })
            ->where(function ($q) use ($windowOpenedAt, $windowExpiredAt) {
                // Last inbound message was between 22 and 24 hours ago
                $q->whereHas('messages', function ($mq) use ($windowOpenedAt, $windowExpiredAt) {
                    $mq->where('sender_type', 'contact')
                        ->where('created_at', '<=', $windowOpenedAt)   // at least 22 hrs ago
                        ->where('created_at', '>=', $windowExpiredAt);  // not more than 24 hrs ago
                });
            })
            ->where(function ($q) {
                // No nudge sent within the cooldown window
                $cooldownAt = now()->subHours(self::NUDGE_COOLDOWN_HOURS);
                $q->whereNull('auto_engaged_at')
                    ->orWhere('auto_engaged_at', '<=', $cooldownAt);
            })
            ->get()
            ->filter(function (Conversation $conv) {
                // Filter: skip leads in terminal stages (Won, Lost, Closed, etc.)
                $lead = $conv->contact?->leads?->first();
                if (!$lead) return false; // no lead = skip

                $stageName = strtolower($lead->stage?->name ?? '');
                $terminalStages = ['won', 'lost', 'closed', 'junk', 'dead'];
                foreach ($terminalStages as $terminal) {
                    if (str_contains($stageName, $terminal)) {
                        return false;
                    }
                }
                return true;
            });
    }

    /**
     * Build an AI-generated, human-like re-engagement message based on conversation history.
     */
    public function buildNudgeMessage(Conversation $conversation): string
    {
        $business  = $conversation->business;
        $contact   = $conversation->contact;
        $contactName = $contact?->name ?? 'sir';

        // Build greeting based on current time in business timezone
        $timezone = $business->timezone ?? 'UTC';
        $localHour = now()->timezone($timezone)->hour;
        $greeting = $this->getTimeGreeting($localHour);

        // Attempt AI-generated message
        if ($business->ai_api_key) {
            $aiMessage = $this->generateAiNudge($conversation, $greeting, $contactName);
            if ($aiMessage) {
                return $aiMessage;
            }
        }

        // Fallback: template-based nudge
        return $this->getFallbackNudge($greeting, $contactName, $conversation);
    }

    /**
     * Call the configured AI provider to produce a single human-like nudge message.
     */
    protected function generateAiNudge(Conversation $conversation, string $greeting, string $contactName): ?string
    {
        $business = $conversation->business;

        // Fetch last 20 messages for context
        $messages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->take(20)
            ->get();

        $chatHistory = "";
        foreach ($messages as $msg) {
            if ($msg->sender_type === 'system' || $msg->sender_type === 'note') continue;
            $sender = $msg->sender_type === 'contact' ? "Customer" : "Agent/AI";
            $chatHistory .= "{$sender}: {$msg->content}\n";
        }

        if (empty(trim($chatHistory))) {
            return null;
        }

        $lead = $conversation->contact?->leads?->first();
        $dealContext = "";
        if ($lead) {
            if ($lead->req_product) $dealContext .= "Product interested in: {$lead->req_product}. ";
            if ($lead->req_budget)  $dealContext .= "Budget: {$lead->req_budget}. ";
            $dealContext .= "Current stage: " . ($lead->stage?->name ?? 'New Lead') . ".";
        }

        $systemPrompt = $business->ai_system_prompt ?? "You are a WhatsApp sales agent. Be friendly and casual.";

        $prompt = "
You are a WhatsApp sales agent re-engaging a potential customer named '{$contactName}'.
The customer has not replied in a while and their 24-hour WhatsApp session window is about to expire.

Current greeting (based on time of day): {$greeting}
{$dealContext}

CRITICAL LANGUAGE RULE: Read the customer's messages in the conversation history carefully.
Identify the EXACT language and script the customer used (e.g. pure Hindi in Devanagari script,
Hinglish in Roman script, pure English, Punjabi, Tamil, etc.) and write your reply in that EXACT
same language and style. Mirror their vocabulary, tone, and formality level precisely.
If the customer wrote in Roman-script Hindi (e.g. \"bhai kab milega\"), reply in Roman-script Hindi.
If they wrote in pure English, reply in English. Do NOT switch languages.

Based on the conversation history below, write ONE short, natural, human-like re-engagement message (max 2 sentences).
Guidelines:
- Start with the greeting provided
- Sound like a real human sales person, NOT a bot
- Reference the deal/product context naturally if relevant
- Match the customer's exact language, tone, and vocabulary from their messages
- Examples (style varies based on what the customer used):
  * If customer used Hinglish: \"Good morning sir 🙏 Kya status hai deal ka? Koi bhi doubt ho toh batayein.\"
  * If customer used formal English: \"Good afternoon! Just wanted to follow up — have you had a chance to review the quotation?\"
  * If customer used casual Hindi: \"Bhai good morning! Deal ka kya hua, kuch decision hua kya?\"
- Return ONLY the message text. No JSON, no quotes, no explanation.

Conversation history:
{$chatHistory}
";

        try {
            $provider = $business->ai_provider ?? 'openai';
            $model    = $business->ai_model;

            if ($provider === 'openai') {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $business->ai_api_key,
                    'Content-Type'  => 'application/json',
                ])->post('https://api.openai.com/v1/chat/completions', [
                    'model'    => $model ?? 'gpt-4o',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                    'max_tokens'  => 100,
                    'temperature' => 0.8,
                ]);

                if ($response->successful()) {
                    return trim($response->json('choices.0.message.content') ?? '');
                }
                Log::warning("AutoEngage OpenAI error: " . $response->body());

            } elseif ($provider === 'gemini') {
                $modelName = $model ?? 'gemini-1.5-flash';
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . $business->ai_api_key;

                $response = Http::post($url, [
                    'systemInstruction' => ['parts' => [['text' => $systemPrompt]]],
                    'contents'          => [['parts' => [['text' => $prompt]]]],
                    'generationConfig'  => ['maxOutputTokens' => 100, 'temperature' => 0.8],
                ]);

                if ($response->successful()) {
                    return trim($response->json('candidates.0.content.parts.0.text') ?? '');
                }
                Log::warning("AutoEngage Gemini error: " . $response->body());

            } elseif ($provider === 'deepseek') {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $business->ai_api_key,
                    'Content-Type'  => 'application/json',
                ])->post('https://api.deepseek.com/chat/completions', [
                    'model'    => $model ?? 'deepseek-chat',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                    'max_tokens'  => 100,
                    'temperature' => 0.8,
                ]);

                if ($response->successful()) {
                    return trim($response->json('choices.0.message.content') ?? '');
                }
                Log::warning("AutoEngage DeepSeek error: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("AutoEngage AI generation failed: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Fallback nudge when AI is unavailable or fails.
     */
    protected function getFallbackNudge(string $greeting, string $contactName, Conversation $conversation): string
    {
        $lead = $conversation->contact?->leads?->first();
        $product = $lead?->req_product;

        $templates = [
            "{$greeting} {$contactName}! 🙏 Kya status hai? Koi bhi sawaal ho toh batayein, hum yahan hain.",
            "{$greeting}! Just checking in — apka koi bhi query ho toh hum help karne ke liye ready hain. 😊",
            "{$greeting} {$contactName}! Aapka deal ka update lena tha. Kuch discuss karna hai?",
        ];

        if ($product) {
            $templates[] = "{$greeting} sir! {$product} ke baare mein aapka kya socha? Koi update hai? 🙏";
        }

        return $templates[array_rand($templates)];
    }

    /**
     * Send the nudge message and mark the conversation as engaged.
     */
    public function sendNudge(Conversation $conversation, string $nudgeText): void
    {
        // Create message record
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => 'ai',
            'type'            => 'text',
            'content'         => $nudgeText,
            'status'          => 'pending',
        ]);

        // Dispatch to WhatsApp immediately
        SendWhatsAppMessageJob::dispatchSync($message);

        // Mark conversation as engaged
        $conversation->update(['auto_engaged_at' => now()]);

        Log::info("AutoEngage: Nudge sent for conversation #{$conversation->id} to contact #{$conversation->contact_id}");
    }

    /**
     * Get a time-appropriate greeting string.
     */
    public function getTimeGreeting(int $hour): string
    {
        if ($hour >= 5 && $hour < 12) {
            return 'Good morning';
        } elseif ($hour >= 12 && $hour < 17) {
            return 'Good afternoon';
        } elseif ($hour >= 17 && $hour < 21) {
            return 'Good evening';
        } else {
            return 'Hello';
        }
    }

    /**
     * Get count of conversations that are currently eligible for auto-engage.
     * Used by the UI to show live status.
     */
    public function getEligibleCount(): int
    {
        return $this->getEligibleConversations()->count();
    }

    /**
     * Get count of conversations where window will expire within N hours.
     * Used by Shared Inbox badge.
     */
    public function getExpiringConversationIds(int $withinHours = 2): array
    {
        $cutoff = now()->subHours(self::SESSION_WINDOW_HOURS - $withinHours);
        $expired = now()->subHours(self::SESSION_WINDOW_HOURS);

        return Conversation::query()
            ->where('status', 'open')
            ->whereHas('messages', function ($q) use ($cutoff, $expired) {
                $q->where('sender_type', 'contact')
                    ->where('created_at', '<=', $cutoff)
                    ->where('created_at', '>=', $expired);
            })
            ->pluck('id')
            ->toArray();
    }
}
