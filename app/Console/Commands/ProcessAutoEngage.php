<?php

namespace App\Console\Commands;

use App\Services\AutoEngageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessAutoEngage extends Command
{
    protected $signature   = 'wacrm:auto-engage';
    protected $description = 'Send pre-expiry re-engagement messages before the 24-hour WhatsApp session window closes';

    public function handle(AutoEngageService $service): void
    {
        $this->info('[Auto Engage] Starting scan...');

        $conversations = $service->getEligibleConversations();

        if ($conversations->isEmpty()) {
            $this->info('[Auto Engage] No eligible conversations found.');
            return;
        }

        $this->info("[Auto Engage] Found {$conversations->count()} conversation(s) to nudge.");

        $sent   = 0;
        $failed = 0;

        foreach ($conversations as $conversation) {
            try {
                $nudgeText = $service->buildNudgeMessage($conversation);

                if (empty(trim($nudgeText))) {
                    $this->warn("[Auto Engage] Skipping conversation #{$conversation->id} — empty nudge message generated.");
                    continue;
                }

                $service->sendNudge($conversation, $nudgeText);

                $contactName = $conversation->contact?->name ?? "#{$conversation->contact_id}";
                $this->line("  ✅ Sent to {$contactName} (Conv #{$conversation->id}): \"{$nudgeText}\"");
                $sent++;

            } catch (\Exception $e) {
                $failed++;
                $this->error("  ❌ Failed for conversation #{$conversation->id}: " . $e->getMessage());
                Log::error("[Auto Engage] Failed for conversation #{$conversation->id}: " . $e->getMessage());
            }
        }

        $this->info("[Auto Engage] Done. Sent: {$sent}, Failed: {$failed}.");
    }
}
