<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Business;
use App\Models\Distributor;
use App\Models\User;
use App\Models\LeadStage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class BusinessList extends Component
{
    use WithPagination;

    // Filters
    public string $search            = '';
    public string $filterStatus      = '';
    public string $filterPlan        = '';
    public string $filterDistributor = '';

    // Drill-down
    public ?int   $viewingId  = null;
    public string $successMsg = '';

    // Create Business form
    public bool $showCreateForm = false;
    public array $form = [
        'name'           => '',
        'owner_name'     => '',
        'owner_email'    => '',
        'owner_phone'    => '',
        'distributor_id' => '',
        'plan'           => 'starter',
        'status'         => 'trial',
        'currency'       => 'INR',
        'timezone'       => 'Asia/Kolkata',
        'owner_password' => '',
    ];

    public function updatingSearch() { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->form = [
            'name' => '', 'owner_name' => '', 'owner_email' => '',
            'owner_phone' => '', 'distributor_id' => '',
            'plan' => 'starter', 'status' => 'trial',
            'currency' => 'INR', 'timezone' => 'Asia/Kolkata',
            'owner_password' => '',
        ];
        $this->showCreateForm = true;
        $this->successMsg = '';
    }

    public function createBusiness(): void
    {
        $this->validate([
            'form.name'           => 'required|string|max:255',
            'form.owner_name'     => 'required|string|max:255',
            'form.owner_email'    => 'required|email|unique:users,email',
            'form.owner_password' => 'required|string|min:8',
            'form.plan'           => 'required|in:starter,growth,enterprise',
            'form.status'         => 'required|in:active,trial,suspended',
        ], [
            'form.owner_email.unique'    => 'This email is already registered.',
            'form.owner_password.min'    => 'Password must be at least 8 characters.',
        ]);

        DB::transaction(function () {
            // Calculate expires_at based on status selection
            $expiresAt = null;
            if ($this->form['status'] === 'active') {
                $expiresAt = now()->addDays(30);
            } elseif ($this->form['status'] === 'trial') {
                $planModel = \App\Models\Plan::where('slug', $this->form['plan'])->first();
                $trialDays = $planModel ? $planModel->trial_days : 7;
                $expiresAt = now()->addDays($trialDays);
            }

            // 1. Create business
            $business = Business::create([
                'name'           => $this->form['name'],
                'owner_email'    => $this->form['owner_email'],
                'owner_phone'    => $this->form['owner_phone'],
                'distributor_id' => $this->form['distributor_id'] ?: null,
                'plan'           => $this->form['plan'],
                'status'         => $this->form['status'],
                'expires_at'     => $expiresAt,
                'currency'       => $this->form['currency'],
                'timezone'       => $this->form['timezone'],
            ]);

            // 2. Create the owner user
            User::create([
                'business_id' => $business->id,
                'name'        => $this->form['owner_name'],
                'email'       => $this->form['owner_email'],
                'password'    => Hash::make($this->form['owner_password']),
                'role'        => 'owner',
            ]);

            // 3. Seed default pipeline stages
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

        $this->showCreateForm = false;
        $this->successMsg = "Business \"{$this->form['name']}\" created with owner account and default pipeline stages.";
        $this->resetPage();
    }

    public function viewBusiness(int $id): void
    {
        $this->viewingId = $this->viewingId === $id ? null : $id;
    }

    public function updateStatus(int $id, string $status): void
    {
        $business = Business::findOrFail($id);
        $updateData = ['status' => $status];
        
        if ($status === 'active') {
            // Give 30 days active subscription
            $updateData['expires_at'] = now()->addDays(30);
        } elseif ($status === 'trial') {
            // Fetch default plan trial days
            $planModel = \App\Models\Plan::where('slug', $business->plan)->first();
            $trialDays = $planModel ? $planModel->trial_days : 7;
            $updateData['expires_at'] = now()->addDays($trialDays);
        }

        $business->update($updateData);
        $this->successMsg = "Status updated and expiration date adjusted.";
    }

    public function updatePlan(int $id, string $plan): void
    {
        Business::findOrFail($id)->update(['plan' => $plan]);
        $this->successMsg = 'Plan updated.';
    }

    public function render()
    {
        $businesses = Business::with(['distributor'])
            ->withCount(['leads', 'conversations'])
            ->when($this->search, fn($q) => $q->where('name','like','%'.$this->search.'%')
                ->orWhere('owner_email','like','%'.$this->search.'%'))
            ->when($this->filterStatus,      fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterPlan,        fn($q) => $q->where('plan', $this->filterPlan))
            ->when($this->filterDistributor, fn($q) => $q->where('distributor_id', $this->filterDistributor))
            ->latest()->paginate(20);

        $distributors    = Distributor::orderBy('name')->get();
        $viewingBusiness = $this->viewingId
            ? Business::with(['distributor'])->withCount(['leads','conversations'])->find($this->viewingId)
            : null;
        $viewingUsers = $this->viewingId
            ? User::where('business_id', $this->viewingId)->get()
            : collect();

        return view('livewire.super-admin.business-list', compact(
            'businesses', 'distributors', 'viewingBusiness', 'viewingUsers'
        ))->layout('layouts.super-admin');
    }
}
