<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DripCampaignSchedule;
use App\Models\Conversation;
use App\Models\Message;
use App\Jobs\SendWhatsAppMessageJob;
use Illuminate\Support\Facades\Log;

class ProcessDripCampaigns extends Command
{
    protected $signature = 'wacrm:process-drips';
    protected $description = 'Process and send pending scheduled drip campaign messages';

    public function handle(): void
    {
        $now = now();
        $schedules = DripCampaignSchedule::with(['campaign', 'step', 'lead'])
            ->where('status', 'pending')
            ->where('send_at', '<=', $now)
            ->get();

        if ($schedules->isEmpty()) {
            $this->info("No pending drip messages to process.");
            return;
        }

        $this->info("Found " . $schedules->count() . " pending drip messages. Processing...");

        foreach ($schedules as $schedule) {
            $campaign = $schedule->campaign;
            $step = $schedule->step;
            $lead = $schedule->lead;

            if (!$campaign || !$step || !$lead) {
                $schedule->delete();
                continue;
            }

            // 1. Safety check: Verify the campaign is still active
            if ($campaign->status !== 'active') {
                $this->warn("Campaign '{$campaign->name}' is not active. Skipping for now.");
                continue;
            }

            // 2. Safety check: Verify the lead is still in the trigger stage
            if ($lead->stage_id != $campaign->trigger_stage_id) {
                $this->warn("Lead {$lead->id} has changed stages. Cancelling schedule.");
                $schedule->delete();
                continue;
            }

            // 3. Create or find conversation
            $conversation = Conversation::firstOrCreate(
                ['business_id' => $campaign->business_id, 'contact_id' => $lead->contact_id],
                ['status' => 'open']
            );

            // 4. Create database Message
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_type' => 'agent',
                'type' => 'template',
                'content' => $step->template_name,
                'status' => 'pending'
            ]);

            // 5. Dispatch job to send message via WhatsApp Meta API immediately
            try {
                SendWhatsAppMessageJob::dispatchSync($message);
                $schedule->update([
                    'status' => 'sent',
                    'sent_at' => now()
                ]);
                $this->info("Dispatched message for Lead ID {$lead->id} (Template: {$step->template_name})");
            } catch (\Exception $e) {
                $schedule->update(['status' => 'failed']);
                Log::error("Failed to dispatch drip message for schedule {$schedule->id}: " . $e->getMessage());
                $this->error("Failed for Lead ID {$lead->id}: " . $e->getMessage());
            }
        }

        $this->info("Drip processing completed.");
    }
}
