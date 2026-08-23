<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use App\Models\Business;
use App\Models\Distributor;
use App\Models\User;

class Overview extends Component
{
    public function render()
    {
        return view('livewire.super-admin.overview', [
            'totalDistributors'  => Distributor::count(),
            'activeDistributors' => Distributor::where('status', 'active')->count(),
            'totalBusinesses'    => Business::count(),
            'activeBusinesses'   => Business::where('status', 'active')->count(),
            'trialBusinesses'    => Business::where('status', 'trial')->count(),
            'totalUsers'         => User::count(),
            'recentBusinesses'   => Business::with('distributor')->latest()->take(5)->get(),
        ])->layout('layouts.super-admin');
    }
}
