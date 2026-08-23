<div class="p-8 max-w-7xl mx-auto font-sans" x-data="{ showNewBroadcastModal: false }">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">WhatsApp Broadcast & Mass Marketing Campaigns</h1>
            <p class="text-gray-600 mt-1">Send bulk WhatsApp promotional & utility campaigns to segmented contact lists using approved Meta templates.</p>
        </div>

        <button @click="showNewBroadcastModal = true" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg shadow-sm font-semibold flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
            <span>Create New Broadcast</span>
        </button>
    </div>

    @if($statusMessage)
        <div class="mb-6 p-4 rounded-lg flex items-center justify-between {{ $statusType === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' }}">
            <span class="font-semibold">{{ $statusMessage }}</span>
            <button wire:click="$set('statusMessage', null)" class="text-sm font-bold opacity-75 hover:opacity-100">&times;</button>
        </div>
    @endif

    @php
        $totalDelivered = array_sum(array_column($campaigns, 'delivered_count'));
        $totalRead = array_sum(array_column($campaigns, 'read_count'));
        $totalClicked = array_sum(array_column($campaigns, 'clicked_count'));
        $avgReadRate = $totalDelivered > 0 ? round(($totalRead / $totalDelivered) * 100, 1) : 0;
        $avgClickRate = $totalDelivered > 0 ? round(($totalClicked / $totalDelivered) * 100, 1) : 0;
    @endphp

    <!-- Quick Metrics Bar -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Broadcasts Sent</div>
            <div class="text-2xl font-extrabold text-gray-900">{{ count($campaigns) }} Campaigns</div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Messages Delivered</div>
            <div class="text-2xl font-extrabold text-emerald-600">
                {{ $totalDelivered }} Msgs
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Avg Read Rate</div>
            <div class="text-2xl font-extrabold text-blue-600">{{ $avgReadRate }}%</div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Avg Click-Through Rate</div>
            <div class="text-2xl font-extrabold text-indigo-600">{{ $avgClickRate }}%</div>
        </div>
    </div>

    <!-- Campaigns Performance Table -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h2 class="font-bold text-gray-800 text-lg">Broadcast Campaign Analytics</h2>
            <span class="text-xs text-gray-500 font-medium">Real-Time Meta WhatsApp Analytics</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-100 text-gray-700 uppercase text-[11px] font-bold tracking-wider">
                    <tr>
                        <th class="py-3.5 px-5">Campaign Name</th>
                        <th class="py-3.5 px-5">Meta Template Used</th>
                        <th class="py-3.5 px-5">Target Segment</th>
                        <th class="py-3.5 px-5">Audience</th>
                        <th class="py-3.5 px-5">Delivered</th>
                        <th class="py-3.5 px-5">Read Rate</th>
                        <th class="py-3.5 px-5">Clicks</th>
                        <th class="py-3.5 px-5">Status</th>
                        <th class="py-3.5 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($campaigns as $camp)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="py-4 px-5 font-bold text-gray-900">
                                {{ $camp['name'] }}
                                <span class="block text-[11px] text-gray-400 font-normal mt-0.5">{{ $camp['created_at'] }}</span>
                            </td>
                            <td class="py-4 px-5 font-mono text-xs text-emerald-700 bg-emerald-50 rounded font-semibold">{{ $camp['template_name'] }}</td>
                            <td class="py-4 px-5 font-medium text-gray-700">{{ $camp['segment'] }}</td>
                            <td class="py-4 px-5 font-bold text-gray-900">{{ $camp['total_recipients'] }} contacts</td>
                            <td class="py-4 px-5 text-gray-800 font-semibold">
                                {{ $camp['delivered_count'] }} <span class="text-xs text-gray-400 font-normal">({{ round(($camp['delivered_count'] / max(1, $camp['total_recipients'])) * 100) }}%)</span>
                            </td>
                            <td class="py-4 px-5">
                                <div class="flex items-center space-x-2">
                                    <span class="font-bold text-blue-600">{{ round(($camp['read_count'] / max(1, $camp['delivered_count'])) * 100) }}%</span>
                                    <div class="w-16 bg-gray-200 h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-blue-500 h-1.5" style="width: {{ round(($camp['read_count'] / max(1, $camp['delivered_count'])) * 100) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-5 font-bold text-indigo-600">{{ $camp['clicked_count'] }} clicks</td>
                            <td class="py-4 px-5">
                                @if($camp['status'] === 'COMPLETED')
                                    <span class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-1 rounded-full border border-green-200">COMPLETED</span>
                                @else
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2.5 py-1 rounded-full border border-yellow-200 animate-pulse">PROCESSING</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-right">
                                <button wire:click="viewReport({{ $camp['id'] }})" class="inline-flex items-center space-x-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1.5 rounded-lg border border-blue-200 transition shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    <span>View Report</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-gray-500">
                                <p class="font-medium text-gray-600">No broadcast campaigns found.</p>
                                <p class="text-xs text-gray-400 mt-1">Click "Create New Broadcast" above to launch a campaign.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Broadcast Campaign Modal with Template Selection & Dynamic Parameter Mapping -->
    <div x-show="showNewBroadcastModal" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl max-w-4xl w-full p-6 shadow-2xl space-y-5 my-6 max-h-[90vh] overflow-y-auto" @click.away="showNewBroadcastModal = false">
            <div class="flex items-center justify-between border-b pb-4 border-gray-100">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Launch Bulk WhatsApp Broadcast</h3>
                    <p class="text-xs text-gray-500">Select template, map dynamic variables to contact data, and dispatch campaign.</p>
                </div>
                <button @click="showNewBroadcastModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-2xl">&times;</button>
            </div>

            <form wire:submit.prevent="launchBroadcast" class="space-y-5">
                
                <!-- Campaign Name & Segment -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Campaign Title</label>
                        <input type="text" wire:model="campaignName" placeholder="e.g. Q3 Promotional Flash Sale" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Target Segment</label>
                        <select wire:model.live="targetSegment" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-green-500 outline-none">
                            <option value="all">All Contacts</option>
                            <option value="stage">Filter by Pipeline Stage</option>
                        </select>
                    </div>
                </div>

                @if($targetSegment === 'stage')
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Select Pipeline Stage</label>
                        <select wire:model.live="selectedStageId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-green-500 outline-none">
                            <option value="">-- Select Stage --</option>
                            @foreach($stages as $stg)
                                <option value="{{ $stg->id }}">{{ $stg->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Audience Counter Badge -->
                <div class="p-3 bg-emerald-50 rounded-lg border border-emerald-200 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        <span class="text-xs text-emerald-900 font-semibold">Resolved Audience Size:</span>
                    </div>
                    <span class="text-sm font-extrabold text-emerald-700 bg-white px-3 py-1 rounded-full border border-emerald-300 shadow-sm">
                        {{ $this->targetAudienceCount }} Target Contacts
                    </span>
                </div>

                <!-- Template Selector & Live Preview Box -->
                <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50 space-y-3">
                    <label class="block text-xs font-bold text-gray-800 uppercase">1. Select Approved Meta Template</label>
                    
                    <select wire:model.live="selectedTemplateName" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm bg-white font-semibold text-gray-900 focus:ring-2 focus:ring-green-500 outline-none">
                        @foreach($availableTemplates as $t)
                            <option value="{{ $t['name'] }}">{{ $t['name'] }} ({{ $t['category'] ?? 'MARKETING' }})</option>
                        @endforeach
                    </select>

                    <!-- Template Live Message Preview -->
                    @if($this->selectedTemplate)
                        <div class="pt-2">
                            <span class="block text-[11px] font-bold text-gray-500 uppercase mb-1.5">Live Template Message Content:</span>
                            <div class="bg-[#e5ddd5] p-3.5 rounded-lg border border-gray-300 max-w-md">
                                <div class="bg-white p-3 rounded-lg shadow-sm text-xs text-gray-800 space-y-1.5 border border-gray-200">
                                    @foreach($this->selectedTemplate['components'] as $c)
                                        @if(($c['type'] ?? '') === 'HEADER')
                                            <div class="font-bold text-gray-900 border-b pb-1 border-gray-100">{{ $c['text'] }}</div>
                                        @elseif(($c['type'] ?? '') === 'BODY')
                                            <div class="whitespace-pre-line text-gray-800">{{ $c['text'] }}</div>
                                        @elseif(($c['type'] ?? '') === 'FOOTER')
                                            <div class="text-[10px] text-gray-400 pt-1 border-t border-gray-100">{{ $c['text'] }}</div>
                                        @elseif(($c['type'] ?? '') === 'BUTTONS')
                                            <div class="pt-1 border-t border-gray-100 space-y-1 mt-1">
                                                @foreach($c['buttons'] as $b)
                                                    <div class="w-full bg-gray-50 border border-gray-200 text-emerald-600 font-semibold py-1 rounded text-center text-[11px]">{{ $b['text'] }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Dynamic Parameter Mappings -->
                @if(!empty($templateVariables))
                    <div class="border border-emerald-200 rounded-xl p-4 bg-emerald-50/40 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-emerald-900 uppercase">2. Dynamic Variable Mappings</label>
                            <span class="text-[10px] text-emerald-700 font-semibold">Map variables to contact attributes</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                            @foreach($templateVariables as $v)
                                <div class="bg-white p-3 rounded-lg border border-emerald-200 flex items-center justify-between">
                                    <span class="font-mono font-bold text-emerald-800">Value for &#123;&#123;{{ $v }}&#125;&#125;:</span>
                                    <select wire:model="paramValues.{{ $v }}" class="border border-gray-300 rounded px-2.5 py-1 text-xs bg-white focus:ring-2 focus:ring-green-500 outline-none">
                                        <option value="contact_name">Contact Full Name</option>
                                        <option value="contact_phone">Contact Phone Number</option>
                                        <option value="company">Contact Company</option>
                                        <option value="custom_text">Default Sample Text</option>
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="pt-4 flex items-center justify-end space-x-3 border-t border-gray-100">
                    <button type="button" @click="showNewBroadcastModal = false" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                    <button type="submit" @click="showNewBroadcastModal = false" class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold px-6 py-2.5 rounded-lg shadow-sm transition flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        <span>Dispatch Broadcast Campaign Now</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Detailed Campaign Analytics & Recipient Logs Modal -->
    @if($showReportModal && $selectedCampaignReport)
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4 overflow-y-auto font-sans">
            <div class="bg-white rounded-2xl max-w-4xl w-full p-6 shadow-2xl space-y-6 my-6 max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div class="flex items-center justify-between border-b pb-4 border-gray-100">
                    <div>
                        <div class="flex items-center space-x-3">
                            <h2 class="text-xl font-bold text-gray-900">{{ $selectedCampaignReport['name'] }}</h2>
                            <span class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-0.5 rounded-full border border-green-200">{{ $selectedCampaignReport['status'] }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Meta Template: <span class="font-mono font-semibold text-emerald-700">{{ $selectedCampaignReport['template_name'] }}</span> | Sent on {{ $selectedCampaignReport['created_at'] }}</p>
                    </div>
                    <button wire:click="closeReportModal" class="text-gray-400 hover:text-gray-600 font-bold text-2xl">&times;</button>
                </div>

                <!-- Metrics Overview Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
                        <span class="text-xs text-gray-500 font-semibold uppercase tracking-wider block mb-1">Target Audience</span>
                        <span class="text-xl font-extrabold text-gray-900">{{ $selectedCampaignReport['total_recipients'] }} Contacts</span>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center">
                        <span class="text-xs text-emerald-700 font-semibold uppercase tracking-wider block mb-1">Delivered</span>
                        <span class="text-xl font-extrabold text-emerald-700">{{ $selectedCampaignReport['delivered_count'] }}</span>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
                        <span class="text-xs text-blue-700 font-semibold uppercase tracking-wider block mb-1">Read Count</span>
                        <span class="text-xl font-extrabold text-blue-700">{{ $selectedCampaignReport['read_count'] }}</span>
                    </div>
                    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 text-center">
                        <span class="text-xs text-indigo-700 font-semibold uppercase tracking-wider block mb-1">Clicks</span>
                        <span class="text-xl font-extrabold text-indigo-700">{{ $selectedCampaignReport['clicked_count'] }}</span>
                    </div>
                </div>

                <!-- Recipient Delivery Logs Table -->
                <div>
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3">Recipient Broadcast Log ({{ count($campaignReportLogs) }} Contacts)</h3>
                    <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                        <div class="max-h-72 overflow-y-auto">
                            <table class="w-full text-left text-sm text-gray-600">
                                <thead class="bg-gray-100 text-gray-700 uppercase text-[11px] font-bold sticky top-0">
                                    <tr>
                                        <th class="py-2.5 px-4">Contact Name</th>
                                        <th class="py-2.5 px-4">WhatsApp Phone</th>
                                        <th class="py-2.5 px-4">Delivery Status</th>
                                        <th class="py-2.5 px-4 text-right">Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($campaignReportLogs as $log)
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-3 px-4 font-semibold text-gray-900">{{ $log['contact_name'] }}</td>
                                            <td class="py-3 px-4 font-mono text-xs text-gray-700">{{ $log['contact_phone'] }}</td>
                                            <td class="py-3 px-4">
                                                <span class="bg-emerald-100 text-emerald-800 text-[11px] font-extrabold px-2 py-0.5 rounded border border-emerald-200">
                                                    {{ $log['status'] }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-xs text-gray-500 text-right">{{ $log['sent_at'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-6 text-center text-gray-400">No recipient logs found for this broadcast.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Button -->
                <div class="flex justify-end pt-3 border-t border-gray-100">
                    <button wire:click="closeReportModal" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-5 py-2 rounded-xl transition text-sm">
                        Close Report
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
