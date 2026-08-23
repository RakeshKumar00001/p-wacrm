<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Business;
use Illuminate\Support\Facades\Http;

class TemplateManager extends Component
{
    public $templates = [];
    public $name = '';
    public $category = 'MARKETING';
    public $language = 'en_US';
    public $headerText = '';
    public $bodyText = '';
    public $footerText = '';

    // Buttons configuration
    public $buttonType = 'NONE'; // NONE, QUICK_REPLY, CALL_TO_ACTION
    public $button1Text = '';
    public $button2Text = '';
    public $websiteUrl = '';
    public $phoneNumber = '';

    public $statusMessage = null;
    public $statusType = 'info';

    public function mount()
    {
        $this->syncTemplatesFromMeta();
    }

    public function syncTemplatesFromMeta()
    {
        $business = auth()->user()->business;
        if (!$business || !$business->waba_id || !$business->whatsapp_access_token) {
            $this->statusMessage = "Using offline template manager. Configure API key in 'WhatsApp API Setup' for live sync.";
            $this->statusType = 'info';
            $this->loadMockTemplates();
            return;
        }

        try {
            $response = Http::withToken($business->whatsapp_access_token)
                ->get("https://graph.facebook.com/v19.0/{$business->waba_id}/message_templates");

            if ($response->successful()) {
                $this->templates = $response->json('data', []);
                $this->statusMessage = "Templates synced live from Meta Graph API!";
                $this->statusType = 'success';
            } else {
                $this->statusMessage = "Offline Mode: " . $response->json('error.message', 'Meta API credentials check');
                $this->statusType = 'info';
                $this->loadMockTemplates();
            }
        } catch (\Exception $e) {
            $this->statusMessage = "Offline Mode: " . $e->getMessage();
            $this->statusType = 'info';
            $this->loadMockTemplates();
        }
    }

    public function loadMockTemplates()
    {
        $this->templates = [
            [
                'id' => '1029384756',
                'name' => 'lead_welcome_offer',
                'category' => 'MARKETING',
                'language' => 'en_US',
                'status' => 'APPROVED',
                'components' => [
                    ['type' => 'HEADER', 'format' => 'TEXT', 'text' => 'Welcome to Acme Auto!'],
                    ['type' => 'BODY', 'text' => "Hi John, thanks for inquiring about our CNC Router Machine.\n\nClaim your exclusive 10% discount quote today!"],
                    ['type' => 'FOOTER', 'text' => 'Reply STOP to opt out.'],
                    [
                        'type' => 'BUTTONS',
                        'buttons' => [
                            ['type' => 'QUICK_REPLY', 'text' => 'Get Instant Quote'],
                            ['type' => 'QUICK_REPLY', 'text' => 'Talk to Agent']
                        ]
                    ]
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
                    ['type' => 'BODY', 'text' => "Your formal PDF quotation is now available online. Click below to view terms and download."],
                    ['type' => 'FOOTER', 'text' => 'Acme Auto Sales Team'],
                    [
                        'type' => 'BUTTONS',
                        'buttons' => [
                            ['type' => 'URL', 'text' => 'View Quotation', 'url' => 'https://acme.com/quote'],
                            ['type' => 'PHONE_NUMBER', 'text' => 'Call Sales', 'phone_number' => '+15550192834']
                        ]
                    ]
                ]
            ],
            [
                'id' => '1029384758',
                'name' => 'urgent_flash_deal',
                'category' => 'MARKETING',
                'language' => 'en_US',
                'status' => 'REJECTED',
                'rejected_reason' => 'PROMOTIONAL_TEXT_VIOLATION',
                'components' => [
                    ['type' => 'BODY', 'text' => 'BUY NOW GET 90% OFF FREE SHIPPING TODAY ONLY!'],
                    [
                        'type' => 'BUTTONS',
                        'buttons' => [
                            ['type' => 'QUICK_REPLY', 'text' => 'Claim 90% Off']
                        ]
                    ]
                ]
            ]
        ];
    }

    public function submitTemplateToMeta()
    {
        $business = auth()->user()->business;

        $components = [
            [
                'type' => 'BODY',
                'text' => $this->bodyText ?: 'Hi John, thank you for contacting us!'
            ]
        ];

        if ($this->headerText) {
            array_unshift($components, [
                'type' => 'HEADER',
                'format' => 'TEXT',
                'text' => $this->headerText
            ]);
        }

        if ($this->footerText) {
            $components[] = [
                'type' => 'FOOTER',
                'text' => $this->footerText
            ];
        }

        // Add Buttons if specified
        if ($this->buttonType === 'QUICK_REPLY' && ($this->button1Text || $this->button2Text)) {
            $buttons = [];
            if ($this->button1Text) $buttons[] = ['type' => 'QUICK_REPLY', 'text' => $this->button1Text];
            if ($this->button2Text) $buttons[] = ['type' => 'QUICK_REPLY', 'text' => $this->button2Text];
            $components[] = ['type' => 'BUTTONS', 'buttons' => $buttons];
        } elseif ($this->buttonType === 'CALL_TO_ACTION') {
            $buttons = [];
            if ($this->button1Text && $this->websiteUrl) {
                $buttons[] = ['type' => 'URL', 'text' => $this->button1Text, 'url' => $this->websiteUrl];
            }
            if ($this->button2Text && $this->phoneNumber) {
                $buttons[] = ['type' => 'PHONE_NUMBER', 'text' => $this->button2Text, 'phone_number' => $this->phoneNumber];
            }
            if (!empty($buttons)) {
                $components[] = ['type' => 'BUTTONS', 'buttons' => $buttons];
            }
        }

        $newTmpl = [
            'id' => rand(10000000, 99999999),
            'name' => strtolower(str_replace(' ', '_', $this->name ?: 'new_template')),
            'category' => $this->category,
            'language' => $this->language,
            'status' => 'PENDING',
            'components' => $components
        ];

        if ($business && $business->waba_id && $business->whatsapp_access_token) {
            try {
                $payload = [
                    'name' => $newTmpl['name'],
                    'category' => $this->category,
                    'language' => $this->language,
                    'components' => $components
                ];

                $response = Http::withToken($business->whatsapp_access_token)
                    ->post("https://graph.facebook.com/v19.0/{$business->waba_id}/message_templates", $payload);

                if ($response->successful()) {
                    $this->statusMessage = "Template '{$newTmpl['name']}' submitted live to Meta! Status: PENDING";
                    $this->statusType = 'success';
                } else {
                    $this->statusMessage = "Meta API Error: " . $response->json('error.message');
                    $this->statusType = 'error';
                }
            } catch (\Exception $e) {
                $this->statusMessage = "Submission Exception: " . $e->getMessage();
                $this->statusType = 'error';
            }
        } else {
            $this->statusMessage = "Template '{$newTmpl['name']}' created in offline mode!";
            $this->statusType = 'success';
        }

        array_unshift($this->templates, $newTmpl);
        $this->reset(['name', 'headerText', 'bodyText', 'footerText', 'buttonType', 'button1Text', 'button2Text', 'websiteUrl', 'phoneNumber']);
    }

    public function render()
    {
        return view('livewire.template-manager');
    }
}
