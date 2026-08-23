<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Business;

class UserProfileSettings extends Component
{
    // Profile Info
    public $name = '';
    public $email = '';
    public $phone = '';
    public $role = 'Sales Agent';
    public $currentPassword = '';
    public $newPassword = '';

    // Business Tenant Settings
    public $businessName = '';
    public $businessEmail = '';
    public $industry = 'Automotive & Machinery';
    public $supportPhone = '';
    public $timezone = 'Asia/Kolkata';

    // Team Members
    public $teamMembers = [];
    public $newAgentName = '';
    public $newAgentEmail = '';
    public $newAgentRole = 'Sales Agent';

    public $statusMessage = null;
    public $statusType = 'info';

    public function mount()
    {
        $user = auth()->user();
        if ($user) {
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role = $user->role;
        }

        $business = $user ? $user->business : null;
        if ($business) {
            $this->businessName = $business->name;
            $this->businessEmail = $business->owner_email;
            $this->supportPhone = $business->owner_phone;
            $this->timezone = $business->timezone ?? 'Asia/Kolkata';
        }

        $this->loadTeamMembers();
    }

    public function loadTeamMembers()
    {
        $business = auth()->user()->business;
        if ($business) {
            $this->teamMembers = User::where('business_id', $business->id)->get()->map(function ($u) {
                return [
                    'id'     => $u->id,
                    'name'   => $u->name,
                    'email'  => $u->email,
                    'role'   => ucfirst(str_replace('_', ' ', $u->role)),
                    'status' => 'ACTIVE',
                    'joined' => $u->created_at->format('Y-m-d'),
                ];
            })->toArray();
        } else {
            $this->teamMembers = [];
        }
    }

    public function updateProfile()
    {
        $user = auth()->user();
        if ($user) {
            $user->update([
                'name' => $this->name,
                'email' => $this->email
            ]);
        }

        $this->statusMessage = "User profile updated successfully!";
        $this->statusType = 'success';
    }

    public function updateBusinessSettings()
    {
        $business = auth()->user()->business;
        if ($business) {
            $business->update([
                'name' => $this->businessName,
                'owner_phone' => $this->supportPhone,
                'timezone' => $this->timezone,
            ]);
        }

        $this->statusMessage = "Business profile settings updated successfully!";
        $this->statusType = 'success';
    }

    public function addTeamMember()
    {
        if (!$this->newAgentName || !$this->newAgentEmail) {
            $this->statusMessage = "Please enter both agent name and email!";
            $this->statusType = 'error';
            return;
        }

        $business = auth()->user()->business;
        if (!$business) return;

        // Check seat limit
        $agentCount = User::where('business_id', $business->id)->count();
        $seatLimit = $business->getFeatureLimit('max_agents', 3);
        if ($agentCount >= $seatLimit) {
            $this->statusMessage = "Limit reached! Your current plan supports a maximum of {$seatLimit} agent seats. Please upgrade.";
            $this->statusType = 'error';
            return;
        }

        // Check if email already exists
        if (User::where('email', $this->newAgentEmail)->exists()) {
            $this->statusMessage = "Email already registered!";
            $this->statusType = 'error';
            return;
        }

        $tempPassword = \Illuminate\Support\Str::random(12);

        User::create([
            'business_id' => $business->id,
            'name'        => $this->newAgentName,
            'email'       => $this->newAgentEmail,
            'password'    => bcrypt($tempPassword),
            'role'        => strtolower(str_replace(' ', '_', $this->newAgentRole)),
        ]);

        $this->statusMessage = "Team member '{$this->newAgentName}' added! Temporary Password: {$tempPassword}";
        $this->statusType = 'success';

        $this->reset(['newAgentName', 'newAgentEmail']);
        $this->loadTeamMembers();
    }

    public function render()
    {
        return view('livewire.user-profile-settings');
    }
}
