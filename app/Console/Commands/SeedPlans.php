<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Plan;

class SeedPlans extends Command
{
    protected $signature = 'wacrm:seed-plans';
    protected $description = 'Seed default subscription plans';

    public function handle(): void
    {
        $plans = [
            [
                'slug'       => 'starter',
                'name'       => 'Starter',
                'price'      => 4999.00,
                'trial_days' => 7,
                'features'   => [
                    'capi'         => false,
                    'ai_agent'     => true,
                    'webhooks'     => false,
                    'max_agents'   => 3,
                    'max_whatsapp' => 1,
                ]
            ],
            [
                'slug'       => 'growth',
                'name'       => 'Growth',
                'price'      => 12999.00,
                'trial_days' => 7,
                'features'   => [
                    'capi'         => true,
                    'ai_agent'     => true,
                    'webhooks'     => true,
                    'max_agents'   => 10,
                    'max_whatsapp' => 3,
                ]
            ],
            [
                'slug'       => 'enterprise',
                'name'       => 'Enterprise',
                'price'      => 29999.00,
                'trial_days' => 7,
                'features'   => [
                    'capi'         => true,
                    'ai_agent'     => true,
                    'webhooks'     => true,
                    'max_agents'   => 999,
                    'max_whatsapp' => 999,
                ]
            ],
        ];

        foreach ($plans as $p) {
            Plan::updateOrCreate(['slug' => $p['slug']], $p);
        }

        $this->info('Successfully seeded plans: Starter, Growth, Enterprise.');
    }
}
