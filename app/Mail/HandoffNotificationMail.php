<?php

namespace App\Mail;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HandoffNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $conversation;
    public $user;

    public function __construct(Conversation $conversation, User $user)
    {
        $this->conversation = $conversation;
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'WACRM: Human Handoff Requested for ' . ($this->conversation->contact->name ?? $this->conversation->contact->phone),
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildHtml(),
        );
    }

    protected function buildHtml(): string
    {
        $contactName = $this->conversation->contact->name ?? 'Unknown Contact';
        $contactPhone = $this->conversation->contact->phone;
        $url = url('/inbox?conversation=' . $this->conversation->id);
        
        return "
        <div style='font-family: sans-serif; padding: 20px; color: #333; max-width: 600px; border: 1px solid #e5e7eb; border-radius: 12px;'>
            <h2 style='color: #4f46e5; margin-top: 0;'>🤖 Human Handoff Requested</h2>
            <p>Hello <strong>{$this->user->name}</strong>,</p>
            <p>An AI Agent conversation has requested human attention and needs your handover support.</p>
            
            <div style='background-color: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                <strong>Customer Details:</strong><br>
                • <strong>Name:</strong> {$contactName}<br>
                • <strong>Phone:</strong> {$contactPhone}<br>
            </div>
            
            <p>Please click the button below to view the conversation in your CRM and resume the chat:</p>
            <p style='margin: 25px 0;'><a href='{$url}' style='display: inline-block; background-color: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);'>Open Shared Inbox</a></p>
            
            <hr style='border: 0; border-top: 1px solid #e5e7eb; margin: 30px 0;'>
            <p style='color: #9ca3af; font-size: 11px; margin-bottom: 0;'>This is an automated notification from your WACRM AI Agent.</p>
        </div>
        ";
    }
}
