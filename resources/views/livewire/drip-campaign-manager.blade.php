<div class="p-8 max-w-7xl mx-auto font-sans" x-data="{ showNewCampaignModal: false }">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8 border-b border-[#26354a] pb-6">
        <div>
            <h1 class="text-3xl font-bold text-white flex items-center space-x-3">
                <span class="p-2 bg-gradient-to-tr from-indigo-500 to-purple-600 rounded-xl text-white shadow-lg">💧</span>
                <span>Automated Drip Messaging Campaigns</span>
            </h1>
            <p class="text-slate-400 mt-1">Schedule sequential WhatsApp template sequences triggered when a lead transitions to specific pipeline stages.</p>
        </div>

        <button @click="showNewCampaignModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl shadow-sm font-semibold flex items-center space-x-2 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Create Drip Campaign</span>
        </button>
    </div>

    <!-- Alerts -->
    @if($statusMessage)
        <div class="mb-6 p-4 rounded-xl flex items-center justify-between {{ $statusType === 'success' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' }}">
            <span class="font-semibold text-sm">{{ $statusMessage }}</span>
            <button wire:click="$set('statusMessage', null)" class="text-lg font-bold opacity-70 hover:opacity-100">&times;</button>
        </div>
    @endif

    <!-- Main Workspace -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left Column: Campaigns List (col-span-4) -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white border border-[#26354a] rounded-2xl shadow-xl p-5">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Your Drip Campaigns</h3>
                
                @if(empty($campaigns))
                    <div class="text-center py-8 text-slate-500 text-sm">
                        No drip campaigns configured yet. Click "Create Drip Campaign" to start.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($campaigns as $camp)
                            <div wire:click="selectCampaign({{ $camp['id'] }})" class="p-4 rounded-xl border cursor-pointer transition {{ $selectedCampaignId == $camp['id'] ? 'bg-indigo-600/10 border-indigo-500/50 text-white' : 'bg-slate-900/40 border-[#26354a] text-slate-300 hover:bg-[#131d2b]' }}">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-bold text-sm">{{ $camp['name'] }}</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider {{ $camp['status'] === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($camp['status'] === 'paused' ? 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20' : 'bg-slate-500/10 text-slate-400 border border-slate-500/20') }}">
                                        {{ $camp['status'] }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-xs text-slate-400">
                                    <span>Stage: <strong class="text-slate-200">{{ $camp['trigger_stage']['name'] ?? 'Unknown' }}</strong></span>
                                    <span>{{ count($camp['steps']) }} Steps</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Campaign details & steps (col-span-8) -->
        <div class="lg:col-span-8">
            @if($selectedCampaign)
                <div class="space-y-6">
                    
                    <!-- Stats Card -->
                    <div class="bg-white border border-[#26354a] rounded-2xl p-6 shadow-xl">
                        <div class="flex items-center justify-between border-b border-[#26354a] pb-4 mb-6">
                            <div>
                                <h2 class="text-xl font-bold text-white">{{ $selectedCampaign['name'] }}</h2>
                                <p class="text-xs text-slate-400 mt-1">Triggered when lead enters stage: <strong class="text-indigo-400">{{ $selectedCampaign['trigger_stage']['name'] ?? 'N/A' }}</strong></p>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex items-center space-x-2">
                                @if($selectedCampaign['status'] !== 'active')
                                    <button wire:click="toggleStatus({{ $selectedCampaign['id'] }}, 'active')" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">Activate</button>
                                @endif
                                @if($selectedCampaign['status'] === 'active')
                                    <button wire:click="toggleStatus({{ $selectedCampaign['id'] }}, 'paused')" class="bg-yellow-600 hover:bg-yellow-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">Pause</button>
                                @endif
                                <button wire:click="deleteCampaign({{ $selectedCampaign['id'] }})" onclick="confirm('Are you sure you want to delete this campaign?') || event.stopImmediatePropagation()" class="bg-red-600 hover:bg-red-750 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">Delete</button>
                            </div>
                        </div>

                        <!-- Mini Stats Grid -->
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div class="bg-[#0b0f19]/60 p-4 rounded-xl border border-[#26354a]">
                                <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Pending Drips</div>
                                <div class="text-2xl font-extrabold text-white mt-1">{{ $selectedCampaign['pending_count'] }}</div>
                            </div>
                            <div class="bg-[#0b0f19]/60 p-4 rounded-xl border border-[#26354a]">
                                <div class="text-[10px] uppercase font-bold text-emerald-400 tracking-wider">Sent Drips</div>
                                <div class="text-2xl font-extrabold text-emerald-400 mt-1">{{ $selectedCampaign['sent_count'] }}</div>
                            </div>
                            <div class="bg-[#0b0f19]/60 p-4 rounded-xl border border-[#26354a]">
                                <div class="text-[10px] uppercase font-bold text-red-400 tracking-wider">Failed</div>
                                <div class="text-2xl font-extrabold text-red-400 mt-1">{{ $selectedCampaign['failed_count'] }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Steps Sequence Card -->
                    <div class="bg-white border border-[#26354a] rounded-2xl p-6 shadow-xl">
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-6">Messaging Sequence</h3>
                        
                        @if(empty($selectedCampaign['steps']))
                            <div class="text-center py-10 border-2 border-dashed border-[#26354a] rounded-xl text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="block text-sm">No steps defined for this campaign.</span>
                                <span class="block text-xs mt-1 text-slate-500">Add your first sequence step below.</span>
                            </div>
                        @else
                            <div class="relative pl-6 border-l-2 border-[#26354a] space-y-8 ml-4">
                                @foreach($selectedCampaign['steps'] as $index => $step)
                                    <div class="relative">
                                        <!-- Sequence Dot -->
                                        <span class="absolute -left-[35px] top-1.5 bg-gradient-to-tr from-indigo-500 to-purple-600 text-white rounded-full w-6.5 h-6.5 flex items-center justify-center font-bold text-xs shadow-md border-2 border-[#0b0f19]">
                                            {{ $step['step_number'] }}
                                        </span>
                                        
                                        <!-- Step Panel -->
                                        <div class="bg-[#0b0f19]/50 border border-[#26354a] rounded-xl p-4 flex items-center justify-between">
                                            <div>
                                                <div class="text-[11px] font-bold text-indigo-400 uppercase tracking-widest">
                                                    @if($step['delay_days'] == 0)
                                                        Triggered Immediately
                                                    @else
                                                        Wait {{ $step['delay_days'] }} {{ Str::plural('Day', $step['delay_days']) }}
                                                    @endif
                                                </div>
                                                <div class="text-sm font-bold text-white mt-1">
                                                    Meta Template: <span class="font-mono text-emerald-400">{{ $step['template_name'] }}</span>
                                                </div>
                                            </div>

                                            <button wire:click="deleteStep({{ $step['id'] }})" class="text-red-400 hover:text-red-300 font-semibold text-xs p-1.5 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 rounded-lg transition">
                                                Delete Step
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Add Step Subform -->
                        <div class="mt-8 border-t border-[#26354a] pt-6">
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Add Step to Sequence</h4>
                            
                            <form wire:submit.prevent="addStep" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1.5">WhatsApp Template</label>
                                    <select wire:model="newStepTemplateName" required class="w-full border border-[#26354a] rounded-lg px-3 py-2 text-sm bg-[#0b0f19] text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                                        @foreach($availableTemplates as $t)
                                            <option value="{{ $t['name'] }}">{{ $t['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1.5">Delay (in days)</label>
                                    <input type="number" wire:model="newStepDelayDays" required min="0" class="w-full border border-[#26354a] rounded-lg px-3 py-2 text-sm bg-[#0b0f19] text-white focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="e.g. 3">
                                </div>

                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-750 text-white font-semibold text-sm px-4 py-2.5 rounded-lg shadow-sm transition">
                                    + Add Step to Campaign
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            @else
                <div class="bg-white border border-[#26354a] rounded-2xl p-10 text-center text-slate-400 shadow-xl">
                    <span class="block text-2xl mb-2">👈</span>
                    <span class="block font-bold">Select a campaign to configure its steps and view statistics.</span>
                </div>
            @endif
        </div>

    </div>

    <!-- Create Campaign Modal -->
    <div x-show="showNewCampaignModal" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl border border-[#26354a] max-w-lg w-full p-6 shadow-2xl space-y-5" @click.away="showNewCampaignModal = false">
            <div class="flex items-center justify-between border-b pb-4 border-[#26354a]">
                <div>
                    <h3 class="text-xl font-bold text-white">Create Drip Campaign</h3>
                    <p class="text-xs text-slate-400">Set the campaign title and trigger stage.</p>
                </div>
                <button @click="showNewCampaignModal = false" class="text-slate-400 hover:text-white font-bold text-2xl">&times;</button>
            </div>

            <form wire:submit.prevent="createCampaign" class="space-y-4" @submit="showNewCampaignModal = false">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1.5">Campaign Name</label>
                    <input type="text" wire:model="campaignName" placeholder="e.g. Lost Leads Re-engagement" required class="w-full border border-[#26354a] rounded-lg px-3.5 py-2 text-sm bg-[#0b0f19] text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1.5">Trigger Pipeline Stage</label>
                    <select wire:model="triggerStageId" required class="w-full border border-[#26354a] rounded-lg px-3 py-2 text-sm bg-[#0b0f19] text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        @foreach($leadStages as $stg)
                            <option value="{{ $stg->id }}">{{ $stg->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-4 flex items-center justify-end space-x-3 border-t border-[#26354a]">
                    <button type="button" @click="showNewCampaignModal = false" class="px-4 py-2 text-sm font-semibold text-slate-400 hover:bg-[#131d2b] rounded-lg transition">Cancel</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-6 py-2.5 rounded-lg shadow-sm transition">
                        Create Drip Sequence
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
