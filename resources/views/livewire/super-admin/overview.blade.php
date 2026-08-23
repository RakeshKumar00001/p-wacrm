<div class="p-8 max-w-7xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-white">Platform Overview</h1>
        <p class="text-slate-500 text-sm mt-1">All distributors, businesses and users across the platform.</p>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
        @foreach([
            ['Distributors', $totalDistributors, $activeDistributors.' active', 'from-rose-600 to-orange-600', '🏢'],
            ['Businesses',   $totalBusinesses,   $activeBusinesses.' active',  'from-indigo-600 to-violet-600', '🏬'],
            ['Trial Accounts',$trialBusinesses,  'Needs conversion',           'from-amber-600 to-yellow-500',  '⏳'],
            ['Total Users',  $totalUsers,        'Across all tenants',         'from-emerald-600 to-teal-600',  '👥'],
        ] as [$label,$val,$sub,$grad,$icon])
        <div class="bg-[#0d1220] border border-white/6 rounded-2xl p-5 flex flex-col gap-3">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $label }}</p>
                    <h3 class="text-4xl font-extrabold text-white mt-1">{{ $val }}</h3>
                </div>
                <div class="text-2xl">{{ $icon }}</div>
            </div>
            <div class="text-xs text-slate-500 font-medium border-t border-white/5 pt-3">{{ $sub }}</div>
        </div>
        @endforeach
    </div>

    {{-- Recent Businesses --}}
    <div class="bg-[#0d1220] border border-white/6 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-white">Recently Added Businesses</h2>
            <a href="/super-admin/businesses" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 transition">View all →</a>
        </div>
        <table class="w-full text-sm">
            <thead class="border-b border-white/5">
                <tr class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                    <th class="px-6 py-3 text-left">Business</th>
                    <th class="px-6 py-3 text-left">Distributor</th>
                    <th class="px-6 py-3 text-left">Plan</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Created</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/4">
                @forelse($recentBusinesses as $b)
                <tr class="hover:bg-white/2 transition">
                    <td class="px-6 py-3.5 font-semibold text-white">{{ $b->name }}</td>
                    <td class="px-6 py-3.5 text-slate-400">{{ $b->distributor?->name ?? '—' }}</td>
                    <td class="px-6 py-3.5">
                        <span class="text-[10px] font-bold px-2 py-1 rounded-lg {{ match($b->plan ?? 'starter') { 'enterprise'=>'bg-rose-500/10 text-rose-400', 'growth'=>'bg-violet-500/10 text-violet-400', default=>'bg-slate-500/10 text-slate-400' } }} uppercase tracking-wider">{{ $b->plan ?? 'starter' }}</span>
                    </td>
                    <td class="px-6 py-3.5">
                        <span class="text-[10px] font-bold px-2 py-1 rounded-lg {{ ($b->status ?? 'active') === 'active' ? 'bg-emerald-500/10 text-emerald-400' : (($b->status === 'trial') ? 'bg-amber-500/10 text-amber-400' : 'bg-red-500/10 text-red-400') }} uppercase tracking-wider">{{ $b->status ?? 'active' }}</span>
                    </td>
                    <td class="px-6 py-3.5 text-slate-500 text-xs">{{ $b->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500 text-sm">No businesses yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
