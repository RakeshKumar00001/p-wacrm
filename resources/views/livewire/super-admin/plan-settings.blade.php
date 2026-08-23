<div class="p-8 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Subscription & Plan Settings</h1>
            <p class="text-slate-500 text-sm mt-1">Configure pricing, free trial days, and enable/disable features for each plan.</p>
        </div>
        <button wire:click="createPlan" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl shadow-lg transition flex items-center gap-2">
            <span>+ Create New Plan</span>
        </button>
    </div>

    {{-- Success msg --}}
    @if($successMsg)
    <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-semibold px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        {{ $successMsg }}
    </div>
    @endif

    {{-- Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @foreach($plansData as $id => $p)
        <div class="bg-[#0d1220] border border-white/6 rounded-2xl overflow-hidden flex flex-col justify-between shadow-xl">
            
            {{-- Header/Title --}}
            <div class="px-6 py-5 border-b border-white/5 bg-gradient-to-r from-indigo-600/10 to-violet-600/10">
                <span class="text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 px-2 py-0.5 rounded-md uppercase tracking-wider">
                    {{ $p['slug'] }}
                </span>
                <h3 class="text-xl font-extrabold text-white mt-1.5">{{ $p['name'] }} Plan</h3>
            </div>

            {{-- Body --}}
            <div class="p-6 space-y-5 flex-1">
                
                {{-- Pricing & Trial --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Price (₹/mo)</label>
                        <input type="number" wire:model="plansData.{{ $id }}.price" 
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                    </div>
                    <div>
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Trial (Days)</label>
                        <input type="number" wire:model="plansData.{{ $id }}.trial_days" 
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                    </div>
                </div>

                {{-- Feature limits --}}
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div>
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Max Agents</label>
                        <input type="number" wire:model="plansData.{{ $id }}.max_agents" 
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                    </div>
                    <div>
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Max WA Numbers</label>
                        <input type="number" wire:model="plansData.{{ $id }}.max_whatsapp" 
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                    </div>
                </div>

                <div class="border-t border-white/5 pt-4">
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Feature Toggles</h4>
                    <div class="space-y-3">
                        
                        {{-- Meta CAPI --}}
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" wire:model="plansData.{{ $id }}.capi"
                                   class="w-4 h-4 rounded bg-white/5 border-white/20 text-indigo-500 focus:ring-indigo-500/30">
                            <div>
                                <span class="text-sm font-semibold text-slate-300 group-hover:text-white transition">Meta CAPI & CTWA</span>
                                <p class="text-[10px] text-slate-500">Track Click ID & send server conversions</p>
                            </div>
                        </label>

                        {{-- AI Sales Agent --}}
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" wire:model="plansData.{{ $id }}.ai_agent"
                                   class="w-4 h-4 rounded bg-white/5 border-white/20 text-indigo-500 focus:ring-indigo-500/30">
                            <div>
                                <span class="text-sm font-semibold text-slate-300 group-hover:text-white transition">AI Sales Agent</span>
                                <p class="text-[10px] text-slate-500">GPT bot qualifications & FAQ responses</p>
                            </div>
                        </label>

                        {{-- Webhooks / API --}}
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" wire:model="plansData.{{ $id }}.webhooks"
                                   class="w-4 h-4 rounded bg-white/5 border-white/20 text-indigo-500 focus:ring-indigo-500/30">
                            <div>
                                <span class="text-sm font-semibold text-slate-300 group-hover:text-white transition">Developer Webhooks / API</span>
                                <p class="text-[10px] text-slate-500">REST lead ingest & custom platform triggers</p>
                            </div>
                        </label>

                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-white/5 bg-white/1 flex justify-end">
                <button wire:click="savePlan({{ $id }})"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition shadow-md">
                    💾 Save {{ $p['name'] }} Settings
                </button>
            </div>

        </div>
        @endforeach
    </div>
</div>
