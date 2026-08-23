<?php

namespace App\Jobs;

use App\Models\Business;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $businessId;
    public $contactIds;
    public $templateName;
    public $languageCode;

    public function __construct(int $businessId, array $contactIds, string $templateName, string $languageCode = 'en')
    {
        $this->businessId = $businessId;
        $this->contactIds = $contactIds;
        $this->templateName = $templateName;
        $this->languageCode = $languageCode;
    }

    public function handle(): void
    {
        $business = Business::find($this->businessId);
        if (!$business) return;

        $contacts = Contact::whereIn('id', $this->contactIds)->get();

        foreach ($contacts as $contact) {
            $conversation = Conversation::firstOrCreate(
                ['business_id' => $business->id, 'contact_id' => $contact->id],
                ['status' => 'open']
            );

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_type' => 'agent',
                'type' => 'template',
                'content' => "Template Broadcast: {$this->templateName}",
                'status' => 'pending'
            ]);

            // Dispatch individual message job inline for instant delivery
            SendWhatsAppMessageJob::dispatchSync($message);
        }

        Log::info("Broadcast dispatched for " . count($contacts) . " contacts.");
    }
}
