<div class="h-full p-8 bg-[#f8fafc] overflow-x-auto font-sans main-scrollbar"
     x-data="{ panelOpen: @entangle('showPanel') }">

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-8 border-b border-slate-200/60 pb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Sales Pipeline</h1>
            <p class="text-slate-500 text-sm mt-1.5">Drag leads between stages. Click a card to edit details.</p>
        </div>
        <button class="bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm flex items-center space-x-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Add Pipeline Stage</span>
        </button>
    </div>

    {{-- ── Kanban Board ─────────────────────────────────────────────────── --}}
    <div class="flex space-x-5 h-full pb-10" style="min-width: max-content;">
        @foreach($stages as $stage)
            <div class="w-80 bg-slate-50 border border-slate-200/80 rounded-2xl flex flex-col h-[calc(100vh-220px)] shadow-sm"
                 x-data="{
                     onDrop(event) {
                         const leadId = event.dataTransfer.getData('text/plain');
                         @this.updateLeadStage(leadId, {{ $stage->id }});
                     }
                 }"
                 @dragover.prevent
                 @drop="onDrop($event)">

                {{-- Stage Header --}}
                <div class="p-4 border-b border-slate-100 font-bold flex justify-between items-center"
                     style="border-top: 4px solid {{ $stage->color ?? '#3B82F6' }}; border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                    @php $leadCount = isset($leadsGrouped[$stage->id]) ? count($leadsGrouped[$stage->id]) : 0; @endphp
                    <span class="text-slate-800 text-sm font-extrabold tracking-wide">
                        {{ $stage->name }} <span class="text-slate-500 font-bold font-mono text-xs ml-0.5">({{ $leadCount }})</span>
                    </span>
                    <span class="bg-slate-200/60 text-slate-700 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full">
                        {{ $currencySymbol }}{{ number_format(isset($leadsGrouped[$stage->id]) ? $leadsGrouped[$stage->id]->sum('expected_value') : 0, 0) }}
                    </span>
                </div>

                {{-- Cards --}}
                <div class="p-4 flex-1 overflow-y-auto space-y-4 custom-scrollbar">
                    @if(isset($leadsGrouped[$stage->id]) && count($leadsGrouped[$stage->id]) > 0)
                        @foreach($leadsGrouped[$stage->id] as $lead)
                            <div class="p-4 bg-white border border-slate-200/70 rounded-xl shadow-sm cursor-pointer hover:border-indigo-300 hover:shadow-md transition-all duration-200 group"
                                 draggable="true"
                                 @dragstart="$event.dataTransfer.setData('text/plain', {{ $lead->id }})"
                                 wire:click="openLead({{ $lead->id }})"
                                 @click.stop>

                                {{-- Name --}}
                                <div class="font-extrabold text-slate-800 group-hover:text-indigo-600 transition-colors text-sm">
                                    {{ $lead->contact->name ?? $lead->contact->phone }}
                                </div>

                                {{-- Source Badge --}}
                                @php
                                    $src = $lead->source ?? 'Unknown';
                                    $srcConfig = match(true) {
                                        str_contains($src, 'Meta Lead Ads')  => ['bg' => 'bg-blue-50',   'text' => 'text-blue-700',   'icon' => '📘'],
                                        str_contains($src, 'Meta Ads')       => ['bg' => 'bg-blue-50',   'text' => 'text-blue-700',   'icon' => '📘'],
                                        str_contains($src, 'Google')         => ['bg' => 'bg-red-50',    'text' => 'text-red-700',    'icon' => '🔍'],
                                        str_contains($src, 'TikTok')         => ['bg' => 'bg-pink-50',   'text' => 'text-pink-700',   'icon' => '🎵'],
                                        str_contains($src, 'Instagram')      => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'icon' => '📸'],
                                        str_contains($src, 'WhatsApp')       => ['bg' => 'bg-green-50',  'text' => 'text-green-700',  'icon' => '💬'],
                                        str_contains($src, 'Website')        => ['bg' => 'bg-cyan-50',   'text' => 'text-cyan-700',   'icon' => '🌐'],
                                        str_contains($src, 'Zapier')         => ['bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'icon' => '⚡'],
                                        str_contains($src, 'REST API')       => ['bg' => 'bg-slate-50',  'text' => 'text-slate-600',  'icon' => '🔌'],
                                        default                               => ['bg' => 'bg-slate-50',  'text' => 'text-slate-500',  'icon' => '📋'],
                                    };
                                @endphp
                                <div class="mt-2 flex flex-wrap gap-1">
                                    <span class="inline-flex items-center space-x-1 text-[10px] font-semibold px-1.5 py-0.5 rounded {{ $srcConfig['bg'] }} {{ $srcConfig['text'] }}">
                                        <span>{{ $srcConfig['icon'] }}</span>
                                        <span>{{ $src }}</span>
                                    </span>
                                    @if($lead->utm_campaign)
                                        <span class="inline-flex items-center text-[9px] font-medium text-slate-400 bg-slate-50 px-1 py-0.5 rounded">
                                            📊 {{ Str::limit($lead->utm_campaign, 18) }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Stats --}}
                                <div class="mt-3 flex items-center justify-between text-[11px]">
                                    <div class="flex items-center space-x-1">
                                        <span class="text-slate-400 font-semibold">Score:</span>
                                        @if($lead->lead_score > 70)
                                            <span class="bg-emerald-50 text-emerald-700 border border-emerald-100 px-1.5 py-0.5 rounded font-extrabold">{{ $lead->lead_score }}</span>
                                        @else
                                            <span class="bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-bold">{{ $lead->lead_score }}</span>
                                        @endif
                                    </div>
                                    <div class="text-slate-900 font-extrabold">
                                        {{ $currencySymbol }}{{ number_format($lead->expected_value, 0) }}
                                    </div>
                                </div>

                                {{-- Assigned Agent --}}
                                @if($lead->assignedUser)
                                    <div class="mt-3.5 pt-3 border-t border-slate-50 flex items-center justify-between">
                                        <div class="flex items-center space-x-1.5 text-[10px] text-slate-500 font-semibold bg-slate-50 border border-slate-100 px-2 py-1 rounded-lg">
                                            <div class="w-4 h-4 rounded-full bg-gradient-to-tr from-indigo-500 to-violet-600 flex items-center justify-center text-[7px] font-extrabold text-white uppercase">
                                                {{ substr($lead->assignedUser->name, 0, 2) }}
                                            </div>
                                            <span>{{ $lead->assignedUser->name }}</span>
                                        </div>
                                        <span class="text-[9px] text-slate-400 font-medium">Assigned</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-xs text-slate-400 p-6 border-2 border-dashed border-slate-200 rounded-xl flex flex-col items-center justify-center space-y-2 py-10 bg-slate-50/20">
                            <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            <span>Drop leads here</span>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- Lead Detail Slide-Over Panel                                      --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}

    {{-- Backdrop --}}
    <div x-show="panelOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="$wire.closePanel()"
         class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40"
         style="display:none;"></div>

    {{-- Drawer --}}
    <div x-show="panelOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full opacity-0"
         x-transition:enter-end="translate-x-0 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0 opacity-100"
         x-transition:leave-end="translate-x-full opacity-0"
         class="fixed top-0 right-0 h-full w-[480px] bg-white shadow-2xl z-50 flex flex-col"
         style="display:none;">

        {{-- Drawer Header --}}
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-indigo-600 to-violet-600">
            <div>
                <h2 class="text-white font-extrabold text-lg tracking-tight">Lead Details</h2>
                <p class="text-indigo-200 text-xs mt-0.5">Edit contact & lead information</p>
            </div>
            <div class="flex items-center space-x-2">
                <button wire:click="startChatWithLead" class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm transition flex items-center space-x-1 cursor-pointer">
                    <span>💬 Open Chat</span>
                </button>
                <button wire:click="closePanel"
                        class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Scrollable Body --}}
        <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">

            {{-- ── Contact Info ── --}}
            <div>
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-widest">Contact</h3>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block mb-1">Full Name</label>
                        <input type="text" wire:model="editName" placeholder="Jane Smith"
                               class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-400 outline-none transition bg-slate-50/50">
                        @error('editName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block mb-1">Phone</label>
                        <input type="text" wire:model="editPhone" placeholder="+91..."
                               class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500 outline-none transition bg-slate-50/50">
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block mb-1">Email</label>
                        <input type="email" wire:model="editEmail" placeholder="jane@example.com"
                               class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500 outline-none transition bg-slate-50/50">
                        @error('editEmail') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block mb-1">Company</label>
                        <input type="text" wire:model="editCompany" placeholder="Acme Corp"
                               class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500 outline-none transition bg-slate-50/50">
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block mb-1">City</label>
                        <input type="text" wire:model="editCity" placeholder="Mumbai"
                               class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500 outline-none transition bg-slate-50/50">
                    </div>
                    <div class="col-span-2">
                        <label class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block mb-1">Contact Notes</label>
                        <textarea wire:model="editNotes" placeholder="About this contact..." rows="2"
                                  class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500 outline-none transition bg-slate-50/50 resize-none"></textarea>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100"></div>

            {{-- ── Lead Info ── --}}
            <div>
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-7 h-7 rounded-lg bg-violet-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-widest">Lead</h3>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block mb-1">Source</label>
                        <select wire:model="editSource"
                                class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500 outline-none transition bg-slate-50/50">
                            <option value="">— Select —</option>
                            @foreach(['Meta Lead Ads','Meta Ads (CTWA)','Google Ads','TikTok Ads','Instagram','WhatsApp Inbound','Website Form','Zapier','REST API','Manual'] as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block mb-1">Lead Score (0–100)</label>
                        <input type="number" wire:model="editLeadScore" min="0" max="100"
                               class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500 outline-none transition bg-slate-50/50">
                        @error('editLeadScore') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-2">
                        <label class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block mb-1">Expected Value</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-bold">{{ $currencySymbol }}</span>
                            <input type="number" wire:model="editExpectedValue" min="0" step="100"
                                   class="w-full border border-slate-200 rounded-xl pl-8 pr-3.5 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500 outline-none transition bg-slate-50/50">
                        </div>
                        @error('editExpectedValue') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-2">
                        <label class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block mb-1">Internal Notes</label>
                        <textarea wire:model="editLeadNotes" placeholder="Follow-up notes, remarks..." rows="3"
                                  class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500 outline-none transition bg-slate-50/50 resize-none"></textarea>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100"></div>

            {{-- ── UTM Attribution ── --}}
            <div>
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-widest">UTM Attribution</h3>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block mb-1">utm_source</label>
                        <input type="text" wire:model="editUtmSource" placeholder="google"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-xs text-slate-700 focus:ring-2 focus:ring-emerald-400 outline-none transition bg-slate-50/50">
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block mb-1">utm_medium</label>
                        <input type="text" wire:model="editUtmMedium" placeholder="cpc"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-xs text-slate-700 focus:ring-2 focus:ring-emerald-400 outline-none transition bg-slate-50/50">
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block mb-1">utm_campaign</label>
                        <input type="text" wire:model="editUtmCampaign" placeholder="summer_26"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-xs text-slate-700 focus:ring-2 focus:ring-emerald-400 outline-none transition bg-slate-50/50">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Footer ── --}}
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/80 flex items-center justify-between gap-3">
            @if($saveMessage)
                <span class="text-sm font-semibold text-emerald-600 flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $saveMessage }}</span>
                </span>
            @else
                <span class="text-xs text-slate-400">Changes are saved immediately.</span>
            @endif
            <div class="flex items-center gap-2 ml-auto">
                <button wire:click="closePanel"
                        class="px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button wire:click="saveLead"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 rounded-xl transition shadow-sm flex items-center space-x-2">
                    <span wire:loading.remove wire:target="saveLead">Save Changes</span>
                    <span wire:loading wire:target="saveLead" class="flex items-center space-x-1.5">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        <span>Saving…</span>
                    </span>
                </button>
            </div>
        </div>
    </div>

</div>
