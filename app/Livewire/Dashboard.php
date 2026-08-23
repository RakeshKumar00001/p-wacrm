<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Lead;
use App\Models\LeadStage;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public function render()
    {
        $business = auth()->user()->business;
        if (!$business) {
            return abort(403, 'No active business associated.');
        }

        $totalLeads = Lead::where('business_id', $business->id)->count();
        $pipelineValue = Lead::where('business_id', $business->id)->sum('expected_value');
        $totalRevenue = Lead::where('business_id', $business->id)->sum('final_value');

        $campaignPerformance = Lead::select(
            'campaign_name',
            DB::raw('count(id) as total_leads'),
            DB::raw('sum(case when final_value > 0 then 1 else 0 end) as won_leads'),
            DB::raw('sum(final_value) as revenue')
        )
        ->where('business_id', $business->id)
        ->whereNotNull('campaign_name')
        ->groupBy('campaign_name')
        ->orderBy('revenue', 'desc')
        ->get();

        $currencySymbol = ($business->currency === 'INR') ? '₹' : (($business->currency === 'USD') ? '$' : ($business->currency ?? '$'));

        return view('livewire.dashboard', [
            'totalLeads' => $totalLeads,
            'pipelineValue' => $pipelineValue,
            'totalRevenue' => $totalRevenue,
            'campaignPerformance' => $campaignPerformance,
            'currencySymbol' => $currencySymbol,
        ]);
    }
}
