<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Business;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAgentConfig extends Component
{
    public $businessId;
    public $aiProvider = 'openai';
    public $aiApiKey = '';
    public $aiModel = 'gpt-4o';
    public $aiSystemPrompt = '';
    public $aiReadPreviousChats = false;
    public $autoEngageEnabled = true;
    public $autoEngageEligibleCount = 0;
    public $aiAutoResumeMinutes = 0;

    // Interactive Playground / Test Agent
    public $testMessage = '';
    public $testHistory = '';
    public $playgroundResult = null;
    public $playgroundError = null;

    public $statusMessage = null;
    public $statusType = 'info';

    public function mount()
    {
        \App\Models\Conversation::ensureAiColumnsExist();
        $business = auth()->user()->business;
        if ($business) {
            $this->businessId = $business->id;
            $this->aiProvider = $business->ai_provider ?? 'openai';
            $this->aiApiKey = $business->ai_api_key ?? '';
            $this->aiModel = $business->ai_model ?? ($this->aiProvider === 'openai' ? 'gpt-4o' : ($this->aiProvider === 'gemini' ? 'gemini-1.5-flash' : 'deepseek-chat'));
            $this->aiSystemPrompt = $business->ai_system_prompt ?? "You are a sales qualification AI. Extract structured data from the conversation.";
            $this->aiReadPreviousChats = (bool)($business->ai_read_previous_chats ?? false);
            $this->autoEngageEnabled   = (bool)($business->auto_engage_enabled ?? true);
            $this->aiAutoResumeMinutes = (int)($business->ai_auto_resume_minutes ?? 0);

            // Live count of conversations eligible for auto-engage
            try {
                $this->autoEngageEligibleCount = app(\App\Services\AutoEngageService::class)->getEligibleCount();
            } catch (\Exception $e) {
                $this->autoEngageEligibleCount = 0;
            }
        }
    }

    public function saveSettings()
    {
        \App\Models\Conversation::ensureAiColumnsExist();
        $business = auth()->user()->business;
        if ($business) {
            $business->update([
                'ai_provider' => $this->aiProvider,
                'ai_api_key' => $this->aiApiKey,
                'ai_model' => $this->aiModel,
                'ai_system_prompt'      => $this->aiSystemPrompt,
                'ai_read_previous_chats' => $this->aiReadPreviousChats,
                'auto_engage_enabled'   => $this->autoEngageEnabled,
                'ai_auto_resume_minutes' => (int)$this->aiAutoResumeMinutes,
            ]);

            $this->statusMessage = 'AI Agent settings saved successfully!';
            $this->statusType = 'success';
        } else {
            $this->statusMessage = 'Failed to find business record.';
            $this->statusType = 'error';
        }
    }

    public function selectProvider($provider)
    {
        $this->aiProvider = $provider;
        if ($provider === 'openai') {
            $this->aiModel = 'gpt-4o';
        } elseif ($provider === 'gemini') {
            $this->aiModel = 'gemini-1.5-flash';
        } elseif ($provider === 'deepseek') {
            $this->aiModel = 'deepseek-chat';
        }
    }

    public function applyPreset($preset)
    {
        switch ($preset) {
            case 'sales':
                $this->aiSystemPrompt = "You are a friendly, consultative sales qualification assistant for our business. Your goal is to guide the conversation on WhatsApp naturally, answer questions about our products, and find out: 1) What specific product they want, 2) Their estimated budget, 3) Their timeline for purchasing. Keep responses brief (under 3 sentences) and suitable for WhatsApp.";
                break;
            case 'automotive':
                $this->aiSystemPrompt = "You are a sales agent for an auto dealership. Help the customer find the right car, schedule a test drive, and understand their trade-in or financing budget and timeline. Be professional and encouraging.";
                break;
            case 'saas':
                $this->aiSystemPrompt = "You are a product expert for our SaaS platform. Explain features, qualify lead business size, ask about their technical needs, and steer them toward the right tier (Starter, Pro, Enterprise). Keep messages clear and brief.";
                break;
        }
    }

    public function testAgent()
    {
        $this->playgroundResult = null;
        $this->playgroundError = null;

        if (!$this->aiApiKey) {
            $this->playgroundError = 'An API Key is required to test the AI Agent.';
            return;
        }

        if (empty(trim($this->testMessage))) {
            $this->playgroundError = 'Please enter a test customer message.';
            return;
        }

        $systemPrompt = $this->aiSystemPrompt ?: "You are a sales qualification AI. Extract structured data from the conversation.";
        
        $prompt = "
        Analyze the following conversation and extract lead qualification details (BANT).
        Respond ONLY in raw JSON format with the following keys:
        - lead_score (integer 0-100)
        - req_product (string or null)
        - req_budget (string or null)
        - req_timeline (string or null)
        - recommended_stage (string from: 'New Lead', 'Qualified', 'Quotation Sent')
        - handoff_required (boolean: true if customer is angry or asks for a human)
        - next_reply (string: your suggested response to the customer)

        Conversation history:
        {$this->testHistory}

        New Customer Message:
        Customer: {$this->testMessage}
        ";

        try {
            if ($this->aiProvider === 'openai') {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->aiApiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->aiModel ?: 'gpt-4o',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'response_format' => ['type' => 'json_object']
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $content = $json['choices'][0]['message']['content'] ?? '{}';
                    $this->playgroundResult = json_decode($content, true);
                } else {
                    $this->playgroundError = $response->json('error.message', 'OpenAI API Error: ' . $response->body());
                }
            } elseif ($this->aiProvider === 'gemini') {
                // Call Gemini API (1.5-flash / 1.5-pro etc.)
                // Note: Gemini uses a different request format and expects systemInstruction parameter.
                // It also supports responseMimeType to enforce JSON output.
                $modelName = $this->aiModel ?: 'gemini-1.5-flash';
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$this->aiApiKey}";

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
                    $json = $response->json();
                    $content = $json['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                    $this->playgroundResult = json_decode($content, true);
                } else {
                    $this->playgroundError = $response->json('error.message', 'Gemini API Error: ' . $response->body());
                }
            } elseif ($this->aiProvider === 'deepseek') {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->aiApiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.deepseek.com/chat/completions', [
                    'model' => $this->aiModel ?: 'deepseek-chat',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'response_format' => ['type' => 'json_object']
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $content = $json['choices'][0]['message']['content'] ?? '{}';
                    $this->playgroundResult = json_decode($content, true);
                } else {
                    $this->playgroundError = $response->json('message', 'DeepSeek API Error: ' . $response->body());
                }
            }
        } catch (\Exception $e) {
            $this->playgroundError = 'Connection Exception: ' . $e->getMessage();
            Log::error("Test Agent Error: " . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.ai-agent-config');
    }
}
