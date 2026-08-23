<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\Business;
use App\Models\User;
use App\Models\LeadStage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Register extends Component
{
    public array $form = [
        'business_name' => '',
        'owner_name'    => '',
        'owner_email'   => '',
        'owner_phone'   => '',
        'password'      => '',
        'plan'          => 'starter', // starter or growth
        'signup_type'   => 'trial',   // trial (7 days) or pay (30 days paid)
    ];

    public function mount(): void
    {
        $plan = request()->query('plan');
        if (in_array($plan, ['starter', 'growth'])) {
            $this->form['plan'] = $plan;
        }
    }

    public function register()
    {
        $this->validate([
            'form.business_name' => 'required|string|max:255',
            'form.owner_name'    => 'required|string|max:255',
            'form.owner_email'   => 'required|email|unique:users,email',
            'form.password'      => 'required|string|min:8',
            'form.plan'          => 'required|in:starter,growth',
            'form.signup_type'   => 'required|in:trial,pay',
        ], [
            'form.owner_email.unique' => 'This email is already registered.',
            'form.password.min'       => 'Password must be at least 8 characters.',
        ]);

        $business = null;
        $user = null;

        $trialDays = 7;

        DB::transaction(function () use (&$business, &$user, &$trialDays) {
            // 1. Fetch dynamic plan details
            $planModel = \App\Models\Plan::where('slug', $this->form['plan'])->first();
            $trialDays = $planModel ? $planModel->trial_days : 7;

            // 2. Calculate expiry date
            $expiresAt = $this->form['signup_type'] === 'trial'
                ? now()->addDays($trialDays)
                : now(); // instantly expired until payment is made

            // 3. Create the business
            $business = Business::create([
                'name'        => $this->form['business_name'],
                'owner_email' => $this->form['owner_email'],
                'owner_phone' => $this->form['owner_phone'],
                'plan'        => $this->form['plan'],
                'status'      => $this->form['signup_type'] === 'trial' ? 'trial' : 'active',
                'expires_at'  => $expiresAt,
                'currency'    => 'INR',
                'timezone'    => 'Asia/Kolkata',
            ]);

            // 4. Create the owner user
            $user = User::create([
                'business_id' => $business->id,
                'name'        => $this->form['owner_name'],
                'email'       => $this->form['owner_email'],
                'password'    => Hash::make($this->form['password']),
                'role'        => 'owner',
            ]);

            // 5. Seed default pipeline stages for the new business
            $defaultStages = [
                ['name' => 'New Lead',   'color' => '#6366f1', 'order_index' => 1],
                ['name' => 'Contacted',  'color' => '#f59e0b', 'order_index' => 2],
                ['name' => 'Qualified',  'color' => '#3b82f6', 'order_index' => 3],
                ['name' => 'Proposal',   'color' => '#8b5cf6', 'order_index' => 4],
                ['name' => 'Won',        'color' => '#10b981', 'order_index' => 5],
                ['name' => 'Lost',       'color' => '#ef4444', 'order_index' => 6],
            ];
            foreach ($defaultStages as $stage) {
                LeadStage::create(array_merge($stage, ['business_id' => $business->id]));
            }
        });

        // Log the user in
        Auth::login($user);

        // Redirect
        if ($this->form['signup_type'] === 'trial') {
            return redirect('/dashboard')->with('success', "Your {$trialDays}-day free trial has started!");
        } else {
            return redirect()->route('billing.renew')->with('info', 'Please complete the subscription payment to activate your account.');
        }
    }

    public function render()
    {
        return view('livewire.auth.register')->layout('layouts.plain', ['title' => 'Register']);
    }
}
