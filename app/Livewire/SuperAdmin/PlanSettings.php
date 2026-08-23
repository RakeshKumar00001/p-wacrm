<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use App\Models\Plan;

class PlanSettings extends Component
{
    public array $plansData = [];
    public string $successMsg = '';

    public function mount(): void
    {
        $this->loadPlans();
    }

    public function loadPlans(): void
    {
        $plans = Plan::all();

        if ($plans->isEmpty()) {
            try {
                Plan::create([
                    'name'       => 'Starter',
                    'slug'       => 'starter',
                    'price'      => 999,
                    'trial_days' => 14,
                    'features'   => [
                        'capi'         => true,
                        'ai_agent'     => false,
                        'webhooks'     => false,
                        'max_agents'   => 2,
                        'max_whatsapp' => 1,
                    ]
                ]);

                Plan::create([
                    'name'       => 'Pro',
                    'slug'       => 'pro',
                    'price'      => 2499,
                    'trial_days' => 14,
                    'features'   => [
                        'capi'         => true,
                        'ai_agent'     => true,
                        'webhooks'     => true,
                        'max_agents'   => 5,
                        'max_whatsapp' => 2,
                    ]
                ]);

                Plan::create([
                    'name'       => 'Enterprise',
                    'slug'       => 'enterprise',
                    'price'      => 4999,
                    'trial_days' => 30,
                    'features'   => [
                        'capi'         => true,
                        'ai_agent'     => true,
                        'webhooks'     => true,
                        'max_agents'   => 20,
                        'max_whatsapp' => 5,
                    ]
                ]);

                $plans = Plan::all();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Failed to seed default plans: " . $e->getMessage());
            }
        }

        $this->plansData = [];
        foreach ($plans as $p) {
            $this->plansData[$p->id] = [
                'id'           => $p->id,
                'name'         => $p->name,
                'slug'         => $p->slug,
                'price'        => $p->price,
                'trial_days'   => $p->trial_days,
                'capi'         => (bool) ($p->features['capi'] ?? false),
                'ai_agent'     => (bool) ($p->features['ai_agent'] ?? false),
                'webhooks'     => (bool) ($p->features['webhooks'] ?? false),
                'max_agents'   => (int) ($p->features['max_agents'] ?? 3),
                'max_whatsapp' => (int) ($p->features['max_whatsapp'] ?? 1),
            ];
        }
    }

    public function createPlan(): void
    {
        try {
            $plan = Plan::create([
                'name'       => 'New Plan',
                'slug'       => 'plan-' . time(),
                'price'      => 1999,
                'trial_days' => 14,
                'features'   => [
                    'capi'         => true,
                    'ai_agent'     => true,
                    'webhooks'     => true,
                    'max_agents'   => 5,
                    'max_whatsapp' => 2,
                ]
            ]);

            $this->loadPlans();
            $this->successMsg = "New plan created successfully.";
        } catch (\Throwable $e) {
            $this->successMsg = "Failed to create plan: " . $e->getMessage();
        }
    }

    public function savePlan(int $planId): void
    {
        $data = $this->plansData[$planId] ?? null;
        if (!$data) return;

        $plan = Plan::findOrFail($planId);
        
        $plan->update([
            'price'      => (float) $data['price'],
            'trial_days' => (int) $data['trial_days'],
            'features'   => [
                'capi'         => (bool) $data['capi'],
                'ai_agent'     => (bool) $data['ai_agent'],
                'webhooks'     => (bool) $data['webhooks'],
                'max_agents'   => (int) $data['max_agents'],
                'max_whatsapp' => (int) $data['max_whatsapp'],
            ]
        ]);

        $this->successMsg = "Plan \"{$plan->name}\" settings updated successfully.";
    }

    public function render()
    {
        return view('livewire.super-admin.plan-settings')
            ->layout('layouts.super-admin');
    }
}
