<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function handle(): void
    {
        if (!$this->message) return;

        $conversation = $this->message->conversation ?? \App\Models\Conversation::find($this->message->conversation_id);
        if (!$conversation) {
            Log::error("Cannot send WhatsApp message: Conversation not found for message ID {$this->message->id}");
            $this->message->update(['status' => 'failed']);
            return;
        }

        $contact = $conversation->contact ?? \App\Models\Contact::find($conversation->contact_id);
        $business = $conversation->business ?? \App\Models\Business::find($conversation->business_id ?? 1) ?? \App\Models\Business::first();

        if (!$business) {
            Log::error("Cannot send WhatsApp message: Business not found for conversation ID {$conversation->id}");
            $this->message->update(['status' => 'failed']);
            return;
        }

        if (!$business->phone_number_id || !$business->whatsapp_access_token) {
            Log::error("Missing WhatsApp credentials (phone_number_id or access_token) for business {$business->id}");
            $this->message->update(['status' => 'failed']);
            return;
        }

        $rawPhone = $contact?->phone ?? '';
        $recipientPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        $recipientPhone = ltrim($recipientPhone, '0');

        // Auto-fix 10-digit Indian phone numbers missing 91 country code
        if (strlen($recipientPhone) === 10 && in_array(substr($recipientPhone, 0, 1), ['6', '7', '8', '9'])) {
            $recipientPhone = '91' . $recipientPhone;
        }

        if (empty($recipientPhone)) {
            Log::error("Cannot send WhatsApp message: Contact phone number is invalid ('{$rawPhone}') for message ID {$this->message->id}");
            $this->message->update(['status' => 'failed']);
            return;
        }

        if ($this->message->type === 'template') {
            $templateName = $this->message->content;
            if (str_starts_with($templateName, 'Template Broadcast: ')) {
                $templateName = str_replace('Template Broadcast: ', '', $templateName);
            }
            if (str_starts_with($templateName, 'Template: ')) {
                $templateName = str_replace('Template: ', '', $templateName);
            }
            // If content has multiline preview, extract the template name from first line or slug
            $firstLine = trim(explode("\n", trim($templateName))[0]);
            if (preg_match('/^[a-zA-Z0-9_\-]+/', $firstLine, $matches)) {
                $templateName = $matches[0];
            } else {
                $templateName = $firstLine;
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $recipientPhone,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => 'en_US'
                    ]
                ]
            ];
        } elseif ($this->message->type === 'image') {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $recipientPhone,
                'type' => 'image',
                'image' => [
                    'link' => $this->message->content
                ]
            ];
        } elseif ($this->message->type === 'document') {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $recipientPhone,
                'type' => 'document',
                'document' => [
                    'link' => $this->message->content,
                    'filename' => basename($this->message->content)
                ]
            ];
        } else {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $recipientPhone,
                'type' => 'text',
                'text' => [
                    'body' => $this->message->content
                ]
            ];
        }

        try {
            $accessToken = trim($business->whatsapp_access_token);
            $phoneNumberId = trim($business->phone_number_id);

            $response = Http::withToken($accessToken)
                ->post("https://graph.facebook.com/v19.0/{$phoneNumberId}/messages", $payload);

            if ($response->successful()) {
                $metaId = $response->json('messages.0.id');
                $updateData = ['status' => 'sent'];
                if (!empty($metaId)) {
                    $updateData['meta_message_id'] = $metaId;
                }
                $this->message->update($updateData);
                Log::info("WhatsApp message ID {$this->message->id} successfully sent to {$recipientPhone}. Meta ID: " . ($metaId ?? 'N/A'));
            } else {
                $this->message->update(['status' => 'failed']);
                $errorMsg = $response->json('error.message') ?? $response->body();
                $errorCode = $response->json('error.code') ?? 'N/A';
                
                $failureReason = "Meta API Error ({$errorCode}): {$errorMsg}";
                if ($errorCode == 131047) {
                    $failureReason = "24-Hour Session Expired. You must send a Meta Approved Template message to re-engage this contact.";
                } elseif ($errorCode == 190) {
                    $failureReason = "Meta Access Token Expired or Invalid. Please update your token in WhatsApp Settings.";
                }

                Log::error("WhatsApp Send Failed [Msg #{$this->message->id}, To: {$recipientPhone}]: {$failureReason}");

                // Insert system warning note into the chat so user sees reason in real-time
                try {
                    \App\Models\Message::create([
                        'conversation_id' => $conversation->id,
                        'sender_type'     => 'system',
                        'type'            => 'text',
                        'content'         => "⚠️ Delivery Failed: {$failureReason}",
                        'status'          => 'failed'
                    ]);
                } catch (\Throwable $ex) {}
            }
        } catch (\Throwable $e) {
            $this->message->update(['status' => 'failed']);
            Log::error("WhatsApp API exception (Message ID {$this->message->id}): " . $e->getMessage());

            try {
                \App\Models\Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_type'     => 'system',
                    'type'            => 'text',
                    'content'         => "⚠️ Delivery Failed: " . $e->getMessage(),
                    'status'          => 'failed'
                ]);
            } catch (\Throwable $ex) {}
        }
    }
}
