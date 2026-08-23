<div class="p-8 max-w-7xl mx-auto font-sans relative">
    <!-- Top Mesh Gradient Accent -->
    <div class="absolute top-0 right-1/4 w-80 h-80 bg-indigo-200/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-10 left-10 w-60 h-60 bg-blue-200/10 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Header Section -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200/60 pb-6 relative z-10">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Executive Dashboard</h1>
            <p class="text-slate-500 text-sm mt-1.5">Attribution performance, WhatsApp lead conversions, and real-time CRM sales stats.</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-2 bg-white border border-slate-200/80 rounded-xl px-4 py-2 text-slate-600 shadow-sm text-xs font-semibold">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Live Sync</span>
            <span class="text-slate-300">|</span>
            <span class="text-slate-400">Last updated: {{ now()->format('H:i') }}</span>
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8 relative z-10">
        
        <!-- Total Leads Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/70 p-6 flex flex-col justify-between hover:shadow-md hover:border-slate-300 transition-all duration-200 group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Total Contacts / Leads</p>
                    <h3 class="text-3xl font-extrabold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ number_format($totalLeads) }}</h3>
                </div>
                <div class="p-3 bg-indigo-50/60 text-indigo-500 border border-indigo-100 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-50 flex items-center text-xs text-slate-500">
                <span class="text-emerald-500 font-bold flex items-center mr-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                    100%
                </span>
                <span>leads qualified via WA AI Agent</span>
            </div>
        </div>

        <!-- Pipeline Value Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/70 p-6 flex flex-col justify-between hover:shadow-md hover:border-slate-300 transition-all duration-200 group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">CRM Pipeline Value</p>
                    <h3 class="text-3xl font-extrabold text-slate-900 group-hover:text-blue-600 transition-colors">{{ $currencySymbol }}{{ number_format($pipelineValue) }}</h3>
                </div>
                <div class="p-3 bg-blue-50/60 text-blue-500 border border-blue-100 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-50 flex items-center text-xs text-slate-500">
                <span class="text-blue-500 font-semibold mr-1">Active Deal flow</span>
                <span>distributed across pipeline stages</span>
            </div>
        </div>

        <!-- Total Revenue Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/70 p-6 flex flex-col justify-between hover:shadow-md hover:border-slate-300 transition-all duration-200 group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Total Closed Revenue</p>
                    <h3 class="text-3xl font-extrabold text-emerald-600 group-hover:text-emerald-700 transition-colors">{{ $currencySymbol }}{{ number_format($totalRevenue) }}</h3>
                </div>
                <div class="p-3 bg-emerald-50/60 text-emerald-500 border border-emerald-100 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-50 flex items-center text-xs text-slate-500">
                <span class="text-emerald-600 font-semibold mr-1">Won Deals</span>
                <span>attributed to specific campaigns</span>
            </div>
        </div>
        
    </div>

    <!-- Meta Ads ROI Report Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/70 overflow-hidden relative z-10">
        <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between bg-slate-50/50">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center space-x-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H7c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.04-.42 1.99-1.07 2.25z"/></svg>
                    <span>Meta Ads ROI & Attribution performance</span>
                </h3>
                <p class="text-slate-400 text-xs mt-1">Tracks performance stats for Facebook/Instagram campaigns delivering WhatsApp leads.</p>
            </div>
            <div class="mt-2 sm:mt-0 bg-indigo-50 text-indigo-600 text-[10px] px-2.5 py-1 rounded-full font-extrabold uppercase tracking-wider">
                Attribution Active
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-500">
                <thead class="text-[10px] text-slate-400 uppercase tracking-widest bg-slate-50/20 border-b border-slate-100">
                    <tr>
                        <th scope="col" class="px-6 py-4">Campaign Name</th>
                        <th scope="col" class="px-6 py-4 text-center">Total Leads</th>
                        <th scope="col" class="px-6 py-4 text-center">Won Leads</th>
                        <th scope="col" class="px-6 py-4 text-right">Revenue Generated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($campaignPerformance as $campaign)
                        <tr class="bg-white hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4.5 font-semibold text-slate-800 whitespace-nowrap">
                                {{ $campaign->campaign_name }}
                            </td>
                            <td class="px-6 py-4.5 text-center text-slate-650 font-medium">
                                {{ number_format($campaign->total_leads) }}
                            </td>
                            <td class="px-6 py-4.5 text-center">
                                @if($campaign->won_leads > 0)
                                    <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full border border-emerald-100">
                                        {{ number_format($campaign->won_leads) }}
                                    </span>
                                @else
                                    <span class="bg-slate-100 text-slate-450 text-xs font-bold px-3 py-1 rounded-full">
                                        {{ number_format($campaign->won_leads) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4.5 text-right font-extrabold text-slate-900 text-base">
                                {{ $currencySymbol }}{{ number_format($campaign->revenue, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-400 text-xs">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v4.5m12 3.5a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <span>No campaign attribution data available yet. Ensure Meta parameters (fbc, fbp, campaign_name) are being captured.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
