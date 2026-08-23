<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\User;
use App\Models\Contact;
use App\Services\MetaCapiService;
use Illuminate\Support\Facades\Http;

class SharedInbox extends Component
{
    use WithFileUploads;

    // Filter
    public $activeFilter = 'all'; // all, unassigned, mine, closed, snoozed

    public $conversations = [];
    public $activeConversation = null;
    public $activeLead = null;

    // Form Inputs
    public $messageText = '';
    public $isPrivateNote = false;
    public $mediaFile = null;
    public $newTag = '';

    // Contact Form Inputs
    public $contactName = '';
    public $contactEmail = '';
    public $contactCompany = '';
    public $contactCity = '';
    public $contactNotes = '';

    // Approved Template Picker State
    public $showTemplateModal = false;
    public $approvedTemplates = [];
    public $selectedTemplateName = '';
    public $templateSearch = '';
    public $templateVars = [];
    public $templatePreviewText = '';

    // AI Auto Resume Timer State
    public $aiAutoResumeMinutes = 0;

    // Collections
    public $leadStages = [];
    public $users = [];
    public $quickReplies = [];
    public $aiSuggestions = [];

    public $conversation_id = null;
    public $contact_id = null;

    protected $queryString = [
        'conversation_id' => ['except' => ''],
        'contact_id'      => ['except' => ''],
    ];

    public function mount()
    {
        $businessId = auth()->user()->business_id;
        $this->loadConversations();
        $this->leadStages = LeadStage::where('business_id', $businessId)->orderBy('order_index')->get();
        $this->users = User::where('business_id', $businessId)->get();

        $this->quickReplies = [
            ['label' => '👋 Greeting',   'text' => 'Hello! Thanks for contacting us. How can we help you today?'],
            ['label' => '💳 Payment',    'text' => 'Here is the link to complete your payment: https://rzp.io/l/payment_link'],
            ['label' => '📍 Location',   'text' => 'Visit our office: 123 Main Street, Sector 45, Gurugram.'],
            ['label' => '📋 Pricing',    'text' => 'Here is our pricing catalog: https://wacrm.in/pricing_catalog.pdf'],
            ['label' => '🚀 Book Demo',  'text' => 'Book a free demo: https://calendly.com/wacrm_demo'],
        ];

        $targetConvId = request()->query('conversation_id', $this->conversation_id);
        $targetContactId = request()->query('contact_id', $this->contact_id);

        if ($targetConvId) {
            $this->selectConversation($targetConvId);
        } elseif ($targetContactId) {
            $conv = Conversation::firstOrCreate(
                ['business_id' => $businessId, 'contact_id' => $targetContactId],
                ['status' => 'open', 'unread_count' => 0]
            );
            $this->loadConversations();
            $this->selectConversation($conv->id);
        } elseif (count($this->conversations) > 0) {
            $this->selectConversation($this->conversations->first()->id);
        }
    }

    public function setFilter($filter)
    {
        $this->activeFilter = $filter;
        $this->loadConversations();
        $this->activeConversation = null;
        $this->activeLead = null;
    }

    public function loadConversations()
    {
        $businessId = auth()->user()->business_id;
        $query = Conversation::where('business_id', $businessId)->with(['contact', 'assignedUser', 'messages' => function ($q) {
            $q->latest()->limit(1);
        }])->latest('updated_at');

        $authUserId = auth()->id() ?? 1;

        switch ($this->activeFilter) {
            case 'unassigned':
                $query->whereNull('assigned_user_id')->where('status', 'open');
                break;
            case 'mine':
                $query->where('assigned_user_id', $authUserId)->where('status', 'open');
                break;
            case 'closed':
                $query->where('status', 'closed');
                break;
            case 'snoozed':
                $query->where('status', 'snoozed');
                break;
            default:
                // all open
                break;
        }

        $this->conversations = $query->get();
    }

    public function selectConversation($conversationId)
    {
        $businessId = auth()->user()->business_id;
        $this->activeConversation = Conversation::where('business_id', $businessId)
            ->with('messages', 'contact.leads.stage', 'assignedUser')
            ->find($conversationId);

        if (!$this->activeConversation) {
            $this->activeLead = null;
            return;
        }

        if ($this->activeConversation->contact) {
            $c = $this->activeConversation->contact;
            $this->contactName = $c->name ?? '';
            $this->contactEmail = $c->email ?? '';
            $this->contactCompany = $c->company ?? '';
            $this->contactCity = $c->city ?? '';
            $this->contactNotes = $c->notes ?? '';

            $this->activeLead = $c->leads()
                ->with(['stage', 'activities' => fn($q) => $q->latest()->limit(10)])
                ->first();
        }

        $this->activeConversation->update(['unread_count' => 0]);
        $this->isPrivateNote = false;
        $this->mediaFile = null;
        $this->newTag = '';
    }

    public function updateContactDetails()
    {
        if (!$this->activeConversation || !$this->activeConversation->contact) return;

        $this->activeConversation->contact->update([
            'name'    => $this->contactName ?: null,
            'email'   => $this->contactEmail ?: null,
            'company' => $this->contactCompany ?: null,
            'city'    => $this->contactCity ?: null,
            'notes'   => $this->contactNotes ?: null,
        ]);

        $this->selectConversation($this->activeConversation->id);
    }

    public function createLeadForContact()
    {
        if (!$this->activeConversation || !$this->activeConversation->contact) return;

        $contact = $this->activeConversation->contact;
        $businessId = auth()->user()->business_id;

        $firstStage = LeadStage::where('business_id', $businessId)->orderBy('order_index')->first();

        Lead::create([
            'business_id'      => $businessId,
            'contact_id'       => $contact->id,
            'stage_id'         => $firstStage?->id ?? 1,
            'source'           => 'WhatsApp Inbox',
            'lead_score'       => 50,
            'expected_value'   => 0,
            'assigned_user_id' => $this->activeConversation->assigned_user_id ?? auth()->id(),
        ]);

        $this->selectConversation($this->activeConversation->id);
    }

    public function selectQuickReply($replyText)
    {
        $this->messageText = $replyText;
    }

    public function addTag()
    {
        if (empty(trim($this->newTag)) || !$this->activeConversation || !$this->activeConversation->contact) return;

        $contact = $this->activeConversation->contact;
        $currentTags = array_filter(array_map('trim', explode(',', $contact->tags ?? '')));

        $newTagClean = trim($this->newTag);
        if (!in_array($newTagClean, $currentTags)) {
            $currentTags[] = $newTagClean;
            $contact->tags = implode(',', $currentTags);
            $contact->save();
        }

        $this->newTag = '';
        $this->selectConversation($this->activeConversation->id);
    }

    public function removeTag($tagToRemove)
    {
        if (!$this->activeConversation || !$this->activeConversation->contact) return;

        $contact = $this->activeConversation->contact;
        $currentTags = array_filter(array_map('trim', explode(',', $contact->tags ?? '')));
        $updatedTags = array_filter($currentTags, fn($t) => trim($t) !== trim($tagToRemove));
        $contact->tags = implode(',', $updatedTags);
        $contact->save();

        $this->selectConversation($this->activeConversation->id);
    }

    public function updateLeadAssignedUser($userId)
    {
        if (!$this->activeConversation) return;

        $this->activeConversation->assigned_user_id = $userId ?: null;
        $this->activeConversation->save();

        if ($this->activeLead) {
            $this->activeLead->assigned_user_id = $userId ?: null;
            $this->activeLead->save();
        }

        $userName = $userId ? (User::find($userId)?->name ?? 'Agent') : 'Unassigned';

        Message::create([
            'conversation_id' => $this->activeConversation->id,
            'sender_type' => 'system',
            'type' => 'text',
            'content' => "Conversation assigned to: {$userName}",
        ]);

        $this->selectConversation($this->activeConversation->id);
    }

    public function closeConversation()
    {
        if (!$this->activeConversation) return;
        $this->activeConversation->update(['status' => 'closed']);
        $this->activeConversation = null;
        $this->activeLead = null;
        $this->loadConversations();
    }

    public function reopenConversation()
    {
        if (!$this->activeConversation) return;
        $this->activeConversation->update(['status' => 'open']);
        $this->selectConversation($this->activeConversation->id);
    }

    public function pollMessages()
    {
        $this->loadConversations();
        if ($this->activeConversation) {
            $this->activeConversation->refresh();
            $this->activeConversation->load('messages', 'contact.leads.stage', 'assignedUser');
        }
    }

    public function sendMessage()
    {
        if (!$this->activeConversation) return;
        if (empty(trim($this->messageText)) && !$this->mediaFile) return;

        if ($this->isPrivateNote) {
            Message::create([
                'conversation_id' => $this->activeConversation->id,
                'sender_type' => 'note',
                'sender_id'   => auth()->id() ?? 1,
                'type'        => 'text',
                'content'     => $this->messageText,
                'status'      => 'sent'
            ]);

            $this->activeConversation->touch();
            $this->messageText = '';
            $this->isPrivateNote = false;
            $this->selectConversation($this->activeConversation->id);
            return;
        }

        $fileUrl = null;
        $msgType = 'text';

        if ($this->mediaFile) {
            $path = $this->mediaFile->store('media', 'public');
            $fileUrl = asset('storage/' . $path);
            $ext = strtolower($this->mediaFile->getClientOriginalExtension());
            $msgType = in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp']) ? 'image' : 'document';
        }

        $msg = Message::create([
            'conversation_id' => $this->activeConversation->id,
            'sender_type' => 'agent',
            'sender_id'   => auth()->id() ?? 1,
            'type'        => $msgType,
            'content'     => $fileUrl ?: $this->messageText,
            'status'      => 'pending'
        ]);

        try {
            \App\Jobs\SendWhatsAppMessageJob::dispatchSync($msg);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send WhatsApp message: " . $e->getMessage());
            $msg->update(['status' => 'failed']);
        }

        $this->activeConversation->touch();
        $this->messageText = '';
        $this->mediaFile = null;
        $this->selectConversation($this->activeConversation->id);
    }

    public function updateLeadStage($stageId, MetaCapiService $capiService)
    {
        if (!$this->activeLead) return;

        $this->activeLead->stage_id = $stageId;
        $this->activeLead->save();

        \App\Services\DripCampaignService::handleStageChange($this->activeLead, $stageId);

        $newStage = LeadStage::find($stageId);

        if ($newStage && $newStage->mapped_meta_event) {
            $this->activeLead->activities()->create([
                'business_id' => $this->activeLead->business_id,
                'user_id'     => auth()->id() ?? 1,
                'type'        => 'stage_change',
                'description' => "Stage changed to {$newStage->name}. CAPI: {$newStage->mapped_meta_event}",
            ]);
            $capiService->sendEvent($this->activeLead, $newStage->mapped_meta_event);
        }

        Message::create([
            'conversation_id' => $this->activeConversation->id,
            'sender_type' => 'system',
            'type'        => 'text',
            'content'     => "Lead Stage updated to: " . ($newStage->name ?? 'Unknown'),
        ]);

        $this->selectConversation($this->activeConversation->id);
    }

    public function toggleAi()
    {
        if ($this->activeConversation) {
            Conversation::ensureAiColumnsExist();
            $newStatus = !$this->activeConversation->ai_enabled;
            
            $this->activeConversation->ai_enabled = $newStatus;

            if ($newStatus) {
                // Turning AI back ON: clear auto-resume timer and enable AI
                $this->activeConversation->ai_auto_resume_at = null;
                $this->activeConversation->save();

                Message::create([
                    'conversation_id' => $this->activeConversation->id,
                    'sender_type'     => 'system',
                    'type'            => 'text',
                    'content'         => "🤖 AI Agent re-enabled by " . (auth()->user()->name ?? 'Human Agent') . ". Customers can now request AI auto-replies or handovers again.",
                ]);
            } else {
                // Turning AI OFF (Human Handover)
                $minutes = (int)$this->aiAutoResumeMinutes;
                if ($minutes <= 0) {
                    $minutes = (int)($this->activeConversation->business->ai_auto_resume_minutes ?? 0);
                }

                if ($minutes > 0) {
                    $this->activeConversation->ai_auto_resume_at = now()->addMinutes($minutes);
                } else {
                    $this->activeConversation->ai_auto_resume_at = null;
                }

                $this->activeConversation->ai_handover_at = now();
                $this->activeConversation->save();

                Message::create([
                    'conversation_id' => $this->activeConversation->id,
                    'sender_type'     => 'system',
                    'type'            => 'text',
                    'content'         => "👤 AI Agent handed over to Human Agent by " . (auth()->user()->name ?? 'Agent') . ($minutes > 0 ? " (Auto-resume set for {$minutes} mins)" : ""),
                ]);
            }

            $this->selectConversation($this->activeConversation->id);
        }
    }

    public function setAiAutoResumeTimer($minutes)
    {
        if (!$this->activeConversation) return;

        Conversation::ensureAiColumnsExist();
        $mins = (int)$minutes;
        $this->aiAutoResumeMinutes = $mins;

        if ($mins > 0) {
            $this->activeConversation->ai_auto_resume_at = now()->addMinutes($mins);
            $this->activeConversation->save();

            Message::create([
                'conversation_id' => $this->activeConversation->id,
                'sender_type'     => 'system',
                'type'            => 'text',
                'content'         => "⏱ AI Agent auto-resume timer set for {$mins} minutes (" . $this->activeConversation->ai_auto_resume_at->format('H:i') . ").",
            ]);
        } else {
            $this->activeConversation->ai_auto_resume_at = null;
            $this->activeConversation->save();

            Message::create([
                'conversation_id' => $this->activeConversation->id,
                'sender_type'     => 'system',
                'type'            => 'text',
                'content'         => "⏱ AI Agent auto-resume timer disabled.",
            ]);
        }

        $this->selectConversation($this->activeConversation->id);
    }

    public function cancelAiAutoResume()
    {
        if ($this->activeConversation) {
            $this->activeConversation->update(['ai_auto_resume_at' => null]);
            $this->selectConversation($this->activeConversation->id);
        }
    }

    /**
     * Persist the per-conversation auto-engage toggle change.
     */
    public function saveConversationAutoEngage()
    {
        if ($this->activeConversation) {
            $this->activeConversation->save();
        }
    }

    /**
     * AI Copilot: Generate 3 smart reply suggestions for agents based on chat context.
     */
    public function generateAiSmartReplies()
    {
        if (!$this->activeConversation) return;

        $recentMessages = $this->activeConversation->messages()
            ->latest()
            ->limit(5)
            ->get()
            ->reverse();

        $history = $recentMessages->map(fn($m) => ($m->sender_type === 'contact' ? 'Customer: ' : 'Agent: ') . $m->content)->implode("\n");

        $contactName = $this->activeConversation->contact->name ?? 'Customer';
        $leadSource  = $this->activeLead->source ?? 'WhatsApp';

        $prompt = "You are a top sales assistant copilot. Based on this WhatsApp conversation history with {$contactName} (Lead Source: {$leadSource}):\n\n{$history}\n\nGenerate exactly 3 short, distinct, high-converting reply options for the sales agent to send. Format as JSON array of 3 strings: [\"reply 1\", \"reply 2\", \"reply 3\"]. Respond ONLY with valid JSON.";

        try {
            $aiService = app(\App\Services\AiSalesService::class);
            // Use reflection or direct API call via AiSalesService prompt
            $apiKey = config('services.openrouter.api_key');
            if ($apiKey) {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type'  => 'application/json',
                ])->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model'    => 'google/gemini-2.5-flash',
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                ]);

                if ($response->successful()) {
                    $raw = $response->json()['choices'][0]['message']['content'] ?? '';
                    preg_match('/\[.*\]/s', $raw, $matches);
                    if (!empty($matches[0])) {
                        $parsed = json_decode($matches[0], true);
                        if (is_array($parsed) && count($parsed) > 0) {
                            $this->aiSuggestions = array_slice($parsed, 0, 3);
                            return;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("AI Smart Reply exception: " . $e->getMessage());
        }

        // Fallback options if AI API isn't set or fails
        $this->aiSuggestions = [
            "Hi {$contactName}! Thanks for your message. Would you like to schedule a quick 5-min demo call?",
            "Great question! I'd be happy to share our product details and current special offer with you.",
            "Thanks for following up! Can I get your preferred email address so I can send the proposal over?",
        ];
    }

    public function selectAiSuggestion($text)
    {
        $this->messageText = $text;
    }

    public function openTemplateModal()
    {
        $this->loadApprovedTemplates();
        $this->showTemplateModal = true;
        if (!empty($this->approvedTemplates)) {
            $firstTmpl = $this->approvedTemplates[0]['name'] ?? '';
            if ($firstTmpl) {
                $this->selectTemplateForModal($firstTmpl);
            }
        }
    }

    public function closeTemplateModal()
    {
        $this->showTemplateModal = false;
        $this->selectedTemplateName = '';
        $this->templateVars = [];
        $this->templatePreviewText = '';
        $this->templateSearch = '';
    }

    public function loadApprovedTemplates()
    {
        $business = auth()->user()?->business;
        if ($business && $business->waba_id && $business->whatsapp_access_token) {
            try {
                $response = Http::withToken($business->whatsapp_access_token)
                    ->get("https://graph.facebook.com/v19.0/{$business->waba_id}/message_templates");
                if ($response->successful()) {
                    $raw = $response->json('data', []);
                    $approved = array_filter($raw, fn($t) => ($t['status'] ?? '') === 'APPROVED');
                    if (!empty($approved)) {
                        $this->approvedTemplates = array_values($approved);
                        return;
                    }
                }
            } catch (\Exception $e) {}
        }

        $this->approvedTemplates = [
            [
                'id' => '1029384756',
                'name' => 'lead_welcome_offer',
                'category' => 'MARKETING',
                'language' => 'en_US',
                'status' => 'APPROVED',
                'components' => [
                    ['type' => 'HEADER', 'format' => 'TEXT', 'text' => 'Exclusive Welcome Offer'],
                    ['type' => 'BODY', 'text' => "Hi {{1}}, thanks for inquiring about our {{2}}.\n\nClaim your exclusive 10% discount quotation today!"],
                    ['type' => 'FOOTER', 'text' => 'Acme Auto Solutions'],
                    ['type' => 'BUTTONS', 'buttons' => [['type' => 'QUICK_REPLY', 'text' => 'Claim Quote']]]
                ]
            ],
            [
                'id' => '1029384757',
                'name' => 'quotation_ready_link',
                'category' => 'UTILITY',
                'language' => 'en_US',
                'status' => 'APPROVED',
                'components' => [
                    ['type' => 'HEADER', 'format' => 'TEXT', 'text' => 'Formal Proposal Ready'],
                    ['type' => 'BODY', 'text' => "Hi {{1}}, your formal proposal for {{2}} is ready. Click below to view terms and download."],
                    ['type' => 'FOOTER', 'text' => 'Sales Team'],
                    ['type' => 'BUTTONS', 'buttons' => [['type' => 'URL', 'text' => 'View Proposal', 'url' => 'https://acme.com/quote']]]
                ]
            ],
            [
                'id' => '1029384759',
                'name' => 'appointment_reminder_confirm',
                'category' => 'UTILITY',
                'language' => 'en_US',
                'status' => 'APPROVED',
                'components' => [
                    ['type' => 'HEADER', 'format' => 'TEXT', 'text' => 'Appointment Reminder'],
                    ['type' => 'BODY', 'text' => "Hello {{1}}, this is a friendly reminder for your scheduled demonstration call for {{2}}."],
                    ['type' => 'FOOTER', 'text' => 'Support Desk'],
                    ['type' => 'BUTTONS', 'buttons' => [['type' => 'QUICK_REPLY', 'text' => 'Confirm'], ['type' => 'QUICK_REPLY', 'text' => 'Reschedule']]]
                ]
            ]
        ];
    }

    public function selectTemplateForModal($tmplName)
    {
        $this->selectedTemplateName = $tmplName;
        $this->templateVars = [];

        $tmpl = collect($this->approvedTemplates)->firstWhere('name', $tmplName);
        if (!$tmpl) {
            $this->templatePreviewText = '';
            return;
        }

        $contactName = $this->contactName ?: ($this->activeConversation->contact->name ?? 'Customer');
        $companyName = $this->contactCompany ?: ($this->activeConversation->contact->company ?? 'our CRM products');

        $bodyComp = collect($tmpl['components'] ?? [])->firstWhere('type', 'BODY');
        $headerComp = collect($tmpl['components'] ?? [])->firstWhere('type', 'HEADER');
        $fullText = ($headerComp['text'] ?? '') . ' ' . ($bodyComp['text'] ?? '');

        preg_match_all('/\{\{(\d+)\}\}/', $fullText, $matches);
        if (!empty($matches[1])) {
            $uniqueVars = array_unique($matches[1]);
            sort($uniqueVars);
            foreach ($uniqueVars as $v) {
                if ($v == 1) {
                    $this->templateVars[$v] = $contactName;
                } elseif ($v == 2) {
                    $this->templateVars[$v] = $companyName;
                } else {
                    $this->templateVars[$v] = "Value {$v}";
                }
            }
        }

        $this->buildTemplatePreview();
    }

    public function updatedTemplateVars()
    {
        $this->buildTemplatePreview();
    }

    public function buildTemplatePreview()
    {
        $tmpl = collect($this->approvedTemplates)->firstWhere('name', $this->selectedTemplateName);
        if (!$tmpl) {
            $this->templatePreviewText = '';
            return;
        }

        $parts = [];
        $headerComp = collect($tmpl['components'] ?? [])->firstWhere('type', 'HEADER');
        $bodyComp = collect($tmpl['components'] ?? [])->firstWhere('type', 'BODY');
        $footerComp = collect($tmpl['components'] ?? [])->firstWhere('type', 'FOOTER');

        if (!empty($headerComp['text'])) {
            $hText = $headerComp['text'];
            foreach ($this->templateVars as $varIdx => $val) {
                $hText = str_replace('{{' . $varIdx . '}}', $val, $hText);
            }
            $parts[] = "📌 *" . trim($hText) . "*";
        }

        if (!empty($bodyComp['text'])) {
            $bText = $bodyComp['text'];
            foreach ($this->templateVars as $varIdx => $val) {
                $bText = str_replace('{{' . $varIdx . '}}', $val, $bText);
            }
            $parts[] = trim($bText);
        }

        if (!empty($footerComp['text'])) {
            $parts[] = "_" . trim($footerComp['text']) . "_";
        }

        $this->templatePreviewText = implode("\n\n", $parts);
    }

    public function sendSelectedTemplate()
    {
        if (!$this->activeConversation) return;
        if (!$this->selectedTemplateName) return;

        $tmplName = $this->selectedTemplateName;
        $formattedContent = "Template: {$tmplName}\n\n" . ($this->templatePreviewText ?: $tmplName);

        $msg = Message::create([
            'conversation_id' => $this->activeConversation->id,
            'sender_type'     => 'agent',
            'sender_id'       => auth()->id() ?? 1,
            'type'            => 'template',
            'content'         => $formattedContent,
            'status'          => 'pending'
        ]);

        try {
            \App\Jobs\SendWhatsAppMessageJob::dispatchSync($msg);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send WhatsApp template message: " . $e->getMessage());
            $msg->update(['status' => 'failed']);
        }

        $this->activeConversation->touch();
        $this->closeTemplateModal();
        $this->selectConversation($this->activeConversation->id);
    }

    public function insertTemplateToInput()
    {
        if ($this->templatePreviewText) {
            $this->messageText = $this->templatePreviewText;
        }
        $this->closeTemplateModal();
    }

    public function render()
    {
        // 24-hour session window: check last inbound message time
        $sessionExpiresAt = null;
        $sessionExpired = false;
        if ($this->activeConversation) {
            $lastInbound = $this->activeConversation->messages
                ->where('sender_type', 'contact')
                ->sortByDesc('created_at')
                ->first();
            if ($lastInbound) {
                $sessionExpiresAt = $lastInbound->created_at->addHours(24);
                $sessionExpired   = now()->isAfter($sessionExpiresAt);
            }
        }

        // Get IDs of conversations with expiring windows for badge display
        $expiringConversationIds = [];
        try {
            $expiringConversationIds = app(\App\Services\AutoEngageService::class)
                ->getExpiringConversationIds(2);
        } catch (\Exception $e) {
            // Silently fail — badge is non-critical
        }

        return view('livewire.shared-inbox', [
            'sessionExpiresAt'        => $sessionExpiresAt,
            'sessionExpired'          => $sessionExpired,
            'expiringConversationIds' => $expiringConversationIds,
        ]);
    }
}
