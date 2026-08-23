<div wire:poll.3s="pollMessages"
     class="flex h-full min-h-0 bg-[#f8fafc] overflow-hidden font-sans relative"
     x-data="{ showDetails: false }">

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- Left Column: Conversations List                                    -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <div class="w-72 lg:w-80 bg-white border-r border-slate-200/70 flex flex-col flex-shrink-0 min-h-0">
        {{-- Header --}}
        <div class="p-4 border-b border-slate-200/70 bg-slate-50/50 flex justify-between items-center">
            <h2 class="text-sm font-extrabold text-slate-800 flex items-center space-x-2">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span>Active Inbox</span>
            </h2>
            <span class="text-[10px] bg-indigo-50 text-indigo-700 font-extrabold px-2.5 py-0.5 rounded-full border border-indigo-150 uppercase tracking-wider">Live</span>
        </div>

        {{-- Filter Tabs --}}
        <div class="flex border-b border-slate-200/70 bg-white overflow-x-auto flex-shrink-0">
            @foreach([
                ['all','All'],
                ['unassigned','Unassigned'],
                ['mine','Mine'],
                ['closed','Closed'],
            ] as [$k,$l])
            <button wire:click="setFilter('{{ $k }}')"
                    class="flex-1 py-2.5 text-[10px] font-bold uppercase tracking-wider whitespace-nowrap transition
                    {{ $activeFilter === $k ? 'border-b-2 border-indigo-600 text-indigo-700 bg-indigo-50/40' : 'text-slate-400 hover:text-slate-700 hover:bg-slate-50' }}">
                {{ $l }}
            </button>
            @endforeach
        </div>

        {{-- Conversations List Scrollable --}}
        <div class="flex-1 overflow-y-auto custom-scrollbar divide-y divide-slate-100 min-h-0">
            @forelse($conversations as $conv)
                <div wire:click="selectConversation({{ $conv->id }})" 
                     class="p-4 cursor-pointer hover:bg-slate-50/70 transition-all duration-150 relative {{ $activeConversation?->id === $conv->id ? 'bg-indigo-50/40 border-l-4 border-indigo-600' : 'border-l-4 border-transparent' }}">
                     
                     <div class="flex justify-between items-center mb-1">
                        <span class="font-extrabold text-slate-800 text-xs sm:text-sm truncate pr-2">{{ $conv->contact->name ?? $conv->contact->phone }}</span>
                        <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider whitespace-nowrap">{{ $conv->updated_at->diffForHumans(null, true, true) }}</span>
                    </div>
                    
                    <div class="text-xs text-slate-500 truncate mb-2.5 leading-relaxed">
                        {{ $conv->messages->first()->content ?? 'No messages yet' }}
                    </div>
                    
                    <div class="flex items-center justify-between">
                        @if($conv->assigned_user_id)
                            <span class="text-[9px] bg-slate-100 text-slate-650 px-2 py-0.5 rounded-md font-bold flex items-center space-x-1 border border-slate-200/60">
                                <span>👤</span> <span>{{ \App\Models\User::find($conv->assigned_user_id)->name ?? 'Agent' }}</span>
                            </span>
                        @else
                            <span class="text-[9px] bg-rose-50 text-rose-700 px-2 py-0.5 rounded-md font-bold flex items-center space-x-1 border border-rose-100">
                                <span>⚠️</span> <span>Unassigned</span>
                            </span>
                        @endif

                        <div class="flex items-center space-x-1">
                            @if(in_array($conv->id, $expiringConversationIds ?? []))
                                <span class="inline-flex items-center px-1.5 py-0.5 text-[8px] font-extrabold text-white bg-violet-600 rounded-full"
                                      title="24-hr window closing — Auto Engage queued">
                                    ⚡ AE
                                </span>
                            @endif
                            @if($conv->unread_count > 0)
                                <span class="inline-flex items-center justify-center px-2 py-0.5 text-[9px] font-extrabold text-white bg-rose-600 rounded-full">
                                    {{ $conv->unread_count }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-slate-400 font-semibold">
                    No conversations found.
                </div>
            @endforelse
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- Center Column: Main Chat Area                                     -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <div class="flex-1 flex flex-col bg-white min-h-0 min-w-0">
        @if($activeConversation)
            <!-- Chat Header -->
            <div class="px-5 py-3.5 border-b border-slate-200/70 bg-white flex items-center justify-between shadow-xs z-10 gap-3">
                <div class="flex items-center space-x-3 truncate">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-violet-600 text-white font-extrabold text-sm flex items-center justify-center flex-shrink-0 shadow-xs">
                        {{ strtoupper(substr($activeConversation->contact->name ?? $activeConversation->contact->phone ?? 'W', 0, 2)) }}
                    </div>
                    <div class="truncate">
                        <h2 class="text-sm sm:text-base font-extrabold text-slate-800 truncate">
                            {{ $activeConversation->contact->name ?? 'WhatsApp Guest' }}
                        </h2>
                        <div class="flex items-center space-x-2 mt-0.5">
                            <span class="text-[10px] bg-slate-100 text-slate-500 font-mono px-2 py-0.5 rounded border border-slate-200/50">
                                {{ $activeConversation->contact->phone }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-2 flex-shrink-0">
                    {{-- 24h Session Timer --}}
                    @if(isset($sessionExpiresAt) && $sessionExpiresAt)
                        @if($sessionExpired)
                            <span class="hidden md:inline-flex text-[10px] font-bold px-2.5 py-1 rounded-full bg-red-50 text-red-700 border border-red-200 animate-pulse">
                                ⏰ Session Expired
                            </span>
                        @else
                            <span class="hidden md:inline-flex text-[10px] font-bold px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200"
                                  title="Session window closes at {{ $sessionExpiresAt->format('d M H:i') }}">
                                ⏱ {{ now()->diffForHumans($sessionExpiresAt, ['parts' => 2, 'short' => true]) }} left
                            </span>
                        @endif
                    @endif

                    {{-- AI Toggle Badge/Btn & Auto-Resume Control --}}
                    <div class="flex items-center space-x-1.5">
                        <button wire:click="toggleAi"
                                class="px-2.5 py-1.5 text-xs font-bold rounded-xl border transition flex items-center space-x-1 cursor-pointer {{ $activeConversation->ai_enabled ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100 shadow-xs' : 'bg-amber-50 text-amber-800 border-amber-200 hover:bg-amber-100' }}"
                                title="{{ $activeConversation->ai_enabled ? 'Click to disable AI and handover to human agent' : 'Click to enable AI agent' }}">
                            <span>🤖</span>
                            <span class="hidden sm:inline">AI: {{ $activeConversation->ai_enabled ? 'ON' : 'OFF' }}</span>
                        </button>

                        @if(!$activeConversation->ai_enabled)
                            @if($activeConversation->ai_auto_resume_at && now()->isBefore($activeConversation->ai_auto_resume_at))
                                <span class="inline-flex items-center space-x-1 bg-indigo-50 text-indigo-700 border border-indigo-200 text-[11px] font-bold px-2 py-1 rounded-xl shadow-xs">
                                    <span>⏱ Resumes {{ $activeConversation->ai_auto_resume_at->diffForHumans() }}</span>
                                    <button type="button" wire:click="cancelAiAutoResume" class="text-indigo-400 hover:text-indigo-800 font-extrabold ml-1 cursor-pointer" title="Cancel auto-resume">&times;</button>
                                </span>
                            @else
                                <select wire:change="setAiAutoResumeTimer($event.target.value)"
                                        class="text-[11px] font-semibold border border-slate-200 rounded-xl px-2 py-1 bg-white text-slate-700 focus:ring-1 focus:ring-indigo-500 outline-none cursor-pointer">
                                    <option value="0" {{ $aiAutoResumeMinutes == 0 ? 'selected' : '' }}>⏱ Auto-Resume: Off</option>
                                    <option value="15" {{ $aiAutoResumeMinutes == 15 ? 'selected' : '' }}>In 15m</option>
                                    <option value="30" {{ $aiAutoResumeMinutes == 30 ? 'selected' : '' }}>In 30m</option>
                                    <option value="60" {{ $aiAutoResumeMinutes == 60 ? 'selected' : '' }}>In 1h</option>
                                    <option value="120" {{ $aiAutoResumeMinutes == 120 ? 'selected' : '' }}>In 2h</option>
                                    <option value="240" {{ $aiAutoResumeMinutes == 240 ? 'selected' : '' }}>In 4h</option>
                                    <option value="1440" {{ $aiAutoResumeMinutes == 1440 ? 'selected' : '' }}>In 24h</option>
                                </select>
                            @endif
                        @endif
                    </div>

                    {{-- Reopen/Close Button --}}
                    @if($activeConversation->status === 'closed')
                        <button wire:click="reopenConversation" class="px-3 py-1.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition shadow-xs">
                            ↩ Reopen
                        </button>
                    @else
                        <button wire:click="closeConversation" class="px-3 py-1.5 text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 rounded-xl transition">
                            ✓ Close
                        </button>
                    @endif

                    {{-- Toggle Lead Details Side Panel --}}
                    <button @click="showDetails = !showDetails"
                            :class="showDetails ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                            class="px-3 py-1.5 text-xs font-bold border rounded-xl transition flex items-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="hidden sm:inline">Lead Info</span>
                    </button>
                </div>
            </div>

            <!-- Chat Messages Area -->
            <div id="chat-messages-container"
                 x-data
                 x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight })"
                 x-effect="$nextTick(() => { $el.scrollTop = $el.scrollHeight })"
                 class="flex-1 overflow-y-auto p-5 space-y-4 bg-slate-50/50 custom-scrollbar min-h-0">
                @foreach($activeConversation->messages as $msg)
                    @if($msg->sender_type === 'system')
                        <!-- System Events -->
                        <div class="flex justify-center my-3">
                            <span class="{{ str_contains($msg->content, '⚠️') ? 'bg-red-100 text-red-800 border-red-300' : 'bg-slate-100/90 text-slate-600 border-slate-200/60' }} text-[11px] font-bold px-3.5 py-1.5 rounded-full border shadow-xs flex items-center space-x-1.5">
                                <span>{{ str_contains($msg->content, '⚠️') ? '🚨' : '⚙️' }}</span>
                                <span>{{ $msg->content }}</span>
                            </span>
                        </div>
                    @elseif($msg->sender_type === 'note')
                        <!-- Private Notes -->
                        <div class="flex justify-center my-3 w-full">
                            <div class="bg-amber-50 border border-amber-200/80 text-amber-900 text-xs px-4 py-3 rounded-2xl w-full max-w-xl shadow-xs">
                                <div class="flex justify-between items-center mb-1 font-bold">
                                    <span class="flex items-center space-x-1.5">
                                        <span>📌</span>
                                        <span>Internal Private Note</span>
                                        <span class="text-[9px] bg-amber-100 text-amber-900 px-2 py-0.5 rounded font-extrabold uppercase border border-amber-200/50">By {{ \App\Models\User::find($msg->sender_id)->name ?? 'Agent' }}</span>
                                    </span>
                                    <span class="opacity-75 font-semibold text-[9px] text-amber-700">{{ $msg->created_at->timezone(auth()->user()->business->timezone ?? 'Asia/Kolkata')->format('M d, H:i') }}</span>
                                </div>
                                <p class="text-xs font-semibold leading-relaxed text-amber-950">{{ $msg->content }}</p>
                            </div>
                        </div>
                    @else
                        <!-- Customer or Agent Bubbles -->
                        <div class="flex {{ $msg->sender_type === 'contact' ? 'justify-start' : 'justify-end' }}">
                            <div class="max-w-[80%] lg:max-w-md px-4 py-3 rounded-2xl shadow-xs leading-relaxed {{ $msg->sender_type === 'contact' ? 'bg-white border border-slate-200/80 text-slate-800 rounded-tl-none' : ($msg->sender_type === 'ai' ? 'bg-indigo-50 border border-indigo-150 text-indigo-950 rounded-tr-none' : 'bg-slate-900 text-white rounded-tr-none') }}">
                                @if($msg->type === 'image')
                                    <div class="flex flex-col space-y-1">
                                        <img src="{{ $msg->content }}" class="max-w-xs rounded-xl border shadow-xs cursor-pointer hover:opacity-95" alt="Uploaded Image">
                                    </div>
                                @elseif($msg->type === 'document')
                                    <div class="flex items-center space-x-2.5 bg-slate-50 border border-slate-200/60 p-3 rounded-xl text-xs text-slate-700">
                                        <span class="text-base">📄</span>
                                        <a href="{{ $msg->content }}" target="_blank" class="font-extrabold text-indigo-600 hover:underline truncate max-w-[200px]">
                                            {{ basename($msg->content) }}
                                        </a>
                                    </div>
                                @elseif($msg->type === 'template')
                                    <div class="flex flex-col space-y-1.5">
                                        <div class="flex items-center space-x-1 text-[9px] font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-100/90 border border-emerald-200/80 px-2 py-0.5 rounded-md w-fit shadow-xs">
                                            <span>⚡ Meta Approved Template</span>
                                        </div>
                                        <p class="text-xs sm:text-sm font-medium leading-relaxed whitespace-pre-line">{{ $msg->content }}</p>
                                    </div>
                                @else
                                    <p class="text-xs sm:text-sm font-medium leading-relaxed whitespace-pre-line">{{ $msg->content }}</p>
                                @endif
                                <div class="text-right mt-1.5 flex items-center justify-end space-x-1 opacity-60">
                                    <span class="text-[8px] font-bold uppercase tracking-wider">{{ $msg->created_at->timezone(auth()->user()->business->timezone ?? 'Asia/Kolkata')->format('H:i') }}</span>
                                    @if($msg->sender_type === 'ai')
                                        <span class="text-[8px] font-bold bg-indigo-500/10 px-1.5 py-0.5 rounded text-indigo-650 border border-indigo-500/15">AI</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Chat Input & Quick Tools -->
            <div class="p-4 border-t border-slate-200/70 bg-white space-y-3 flex-shrink-0">
                
                {{-- 24h Session Expired Callout Banner --}}
                @if(isset($sessionExpired) && $sessionExpired)
                    <div class="bg-amber-50 border border-amber-200/90 p-3 rounded-2xl flex items-center justify-between shadow-xs">
                        <div class="flex items-center space-x-2.5">
                            <span class="text-amber-600 text-base">⏰</span>
                            <div>
                                <h4 class="text-xs font-extrabold text-amber-900">24-Hour WhatsApp Session Expired</h4>
                                <p class="text-[11px] text-amber-750 font-medium">Meta requires an approved template message to re-engage this customer.</p>
                            </div>
                        </div>
                        <button type="button" wire:click="openTemplateModal" class="bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs px-3.5 py-1.5 rounded-xl transition shadow-xs whitespace-nowrap cursor-pointer flex items-center space-x-1">
                            <span>📄</span>
                            <span>Select Approved Template</span>
                        </button>
                    </div>
                @endif

                <!-- Quick Options / Available Replies -->
                <div class="flex items-center space-x-2 overflow-x-auto pb-1 custom-scrollbar">
                    <button type="button" wire:click="openTemplateModal"
                            class="text-[10px] sm:text-[11px] bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold px-3 py-1 rounded-full transition shadow-xs cursor-pointer flex items-center space-x-1 whitespace-nowrap">
                        <span>⚡</span>
                        <span>Approved Meta Templates</span>
                    </button>

                    <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest whitespace-nowrap ml-1">Quick Send:</span>
                    @foreach($quickReplies as $reply)
                        <button type="button" wire:click="selectQuickReply('{{ addslashes($reply['text']) }}')"
                                class="text-[10px] sm:text-[11px] bg-slate-50 hover:bg-indigo-50 hover:text-indigo-700 text-slate-650 px-3 py-1 rounded-full border border-slate-200 transition font-semibold whitespace-nowrap shadow-xs cursor-pointer">
                            {{ $reply['label'] }}
                        </button>
                    @endforeach
                </div>

                <!-- Media Upload Preview -->
                @if($mediaFile)
                    <div class="flex items-center justify-between bg-indigo-50/50 border border-indigo-150 p-2.5 rounded-xl text-xs text-indigo-850">
                        <span class="flex items-center space-x-2 font-semibold">
                            <span>📎 File ready to upload:</span>
                            <span class="font-bold font-mono text-indigo-900 bg-white px-2 py-0.5 rounded border border-indigo-100">{{ $mediaFile->getClientOriginalName() }}</span>
                        </span>
                        <button type="button" wire:click="$set('mediaFile', null)" class="text-indigo-650 hover:text-indigo-800 font-extrabold text-xs uppercase tracking-wider">Remove</button>
                    </div>
                @endif

                <form wire:submit.prevent="sendMessage" class="flex flex-col space-y-2.5">
                    <div class="flex space-x-2.5 items-center">
                        
                        <!-- Upload Media Trigger -->
                        <label class="cursor-pointer p-3 bg-slate-50 hover:bg-slate-100 rounded-xl border border-slate-200 text-slate-500 transition flex items-center justify-center shadow-xs" title="Upload Media File">
                            <input type="file" wire:model="mediaFile" class="hidden">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                        </label>

                        <input wire:model="messageText" type="text" placeholder="{{ $isPrivateNote ? 'Type internal private note (agents only)...' : 'Type a WhatsApp message...' }}" 
                               class="flex-1 border-slate-200 rounded-xl shadow-xs focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 px-4 py-2.5 border text-sm text-slate-800 font-medium">
                        
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl transition font-extrabold text-xs uppercase tracking-wider shadow-sm cursor-pointer whitespace-nowrap">
                            Send
                        </button>
                    </div>

                    <div class="flex justify-between items-center px-1">
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" id="private-note-toggle" wire:model="isPrivateNote" class="rounded text-amber-600 focus:ring-amber-500 h-4 w-4 border-slate-300">
                            <label for="private-note-toggle" class="text-xs font-bold text-slate-500 cursor-pointer select-none">
                                📌 Post as Internal Private Note
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        @else
            <div class="flex-1 flex flex-col items-center justify-center bg-slate-50/30 text-slate-400 font-bold text-xs uppercase tracking-widest space-y-3">
                <svg class="w-8 h-8 text-slate-300 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <span>Select a customer conversation to start messaging</span>
            </div>
        @endif
    </div>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- Right Column: Lead Details & CRM Panel (Toggleable / Responsive)   -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <div x-show="showDetails"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-x-4"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-4"
         class="w-72 lg:w-80 bg-white border-l border-slate-200/70 p-5 overflow-y-auto flex-shrink-0 custom-scrollbar min-h-0">
        
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Contact & CRM Details</h3>
            <button @click="showDetails = false" class="text-slate-400 hover:text-slate-600 font-extrabold text-sm p-1 rounded-lg hover:bg-slate-100 transition">&times;</button>
        </div>

        @if($activeConversation && $activeConversation->contact)
            {{-- ── 1. Contact Information Card ── --}}
            <div class="mb-5 bg-slate-50/80 border border-slate-200/80 p-3.5 rounded-xl space-y-3">
                <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                    <span class="text-xs font-extrabold text-slate-800 uppercase tracking-wider flex items-center space-x-1.5">
                        <span>👤</span>
                        <span>CRM Contact</span>
                    </span>
                    <button wire:click="updateContactDetails" class="text-[10px] bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-2 py-1 rounded transition shadow-xs cursor-pointer">
                        Save Changes
                    </button>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Full Name</label>
                    <input type="text" wire:model.defer="contactName" placeholder="Customer Name" class="w-full border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 font-semibold outline-none focus:ring-1 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Phone Number</label>
                    <div class="text-xs font-mono font-bold text-slate-700 bg-white border border-slate-200/60 px-2.5 py-1.5 rounded-lg">
                        {{ $activeConversation->contact->phone }}
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Email</label>
                        <input type="email" wire:model.defer="contactEmail" placeholder="email@domain.com" class="w-full border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 font-semibold outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Company</label>
                        <input type="text" wire:model.defer="contactCompany" placeholder="Company" class="w-full border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 font-semibold outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">City</label>
                    <input type="text" wire:model.defer="contactCity" placeholder="City / Location" class="w-full border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 font-semibold outline-none focus:ring-1 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Contact Notes</label>
                    <textarea wire:model.defer="contactNotes" rows="2" placeholder="Internal notes about contact..." class="w-full border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 font-semibold outline-none focus:ring-1 focus:ring-indigo-500 resize-none"></textarea>
                </div>
            </div>

            {{-- Tags Section --}}
            <div class="mb-5 border-t border-slate-100 pt-4">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Tags / Labels</label>
                
                <form wire:submit.prevent="addTag" class="flex space-x-2 mb-3">
                    <input type="text" wire:model="newTag" placeholder="Add Tag... (VIP, Hot)" class="flex-1 border border-slate-200 rounded-lg text-xs px-2.5 py-1.5 text-slate-800 focus:ring-1 focus:ring-indigo-500 outline-none">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">Add</button>
                </form>

                <div class="flex flex-wrap gap-1.5">
                    @php
                        $tags = array_filter(array_map('trim', explode(',', $activeConversation->contact->tags ?? '')));
                    @endphp
                    @forelse($tags as $tag)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-150 shadow-xs">
                            {{ $tag }}
                            <button type="button" wire:click="removeTag('{{ $tag }}')" class="ml-1 text-indigo-400 hover:text-indigo-700 font-extrabold">&times;</button>
                        </span>
                    @empty
                        <span class="text-xs text-slate-400 font-medium">No tags applied yet.</span>
                    @endforelse
                </div>
            </div>

            {{-- ── 2. Sales Pipeline / Lead Section ── --}}
            @if($activeLead)
                <div class="border-t border-slate-100 pt-4">
                    <h4 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider mb-3.5 flex items-center space-x-1.5">
                        <span>🎯</span>
                        <span>Sales Pipeline Status</span>
                    </h4>

                    <!-- Assigned User / Owner -->
                    <div class="mb-4 bg-slate-50 border border-slate-200/80 p-3 rounded-xl">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Assigned CRM Agent</label>
                        <select wire:change="updateLeadAssignedUser($event.target.value)" class="w-full border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs bg-white text-slate-800 font-semibold outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500">
                            <option value="">-- Unassigned --</option>
                            @foreach($users as $usr)
                                <option value="{{ $usr->id }}" {{ $activeConversation->assigned_user_id == $usr->id ? 'selected' : '' }}>
                                    {{ $usr->name }} ({{ $usr->role }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Stage Changer -->
                    <div class="mb-4">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Lead Pipeline Stage</label>
                        <select wire:change="updateLeadStage($event.target.value)" class="w-full border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs bg-white text-slate-800 font-extrabold outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500">
                            @foreach($leadStages as $stage)
                                <option value="{{ $stage->id }}" {{ $activeLead->stage_id == $stage->id ? 'selected' : '' }}>
                                    {{ $stage->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- AI Copilot Smart Reply Suggestions -->
                    <div class="mb-4 bg-gradient-to-br from-indigo-50/80 to-purple-50/80 border border-indigo-150 p-3 rounded-xl">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-extrabold text-indigo-900 flex items-center space-x-1.5">
                                <span>✨</span>
                                <span>AI Smart Replies</span>
                            </span>
                            <button type="button" wire:click="generateAiSmartReplies" class="text-[9px] bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-2 py-1 rounded-md transition shadow-xs">
                                Generate
                            </button>
                        </div>
                        @if(count($aiSuggestions) > 0)
                            <div class="space-y-2 mt-2">
                                @foreach($aiSuggestions as $idx => $sug)
                                    <div wire:click="selectAiSuggestion('{{ addslashes($sug) }}')" class="bg-white hover:bg-indigo-50/50 border border-indigo-100 p-2 rounded-lg cursor-pointer transition text-xs font-medium text-slate-800 shadow-xs hover:border-indigo-300">
                                        <div class="text-[8px] font-extrabold text-indigo-600 uppercase tracking-wider mb-0.5">Option {{ $idx + 1 }}</div>
                                        "{{ $sug }}"
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-[10px] text-slate-500 font-medium leading-relaxed">Click "Generate" for AI smart replies.</p>
                        @endif
                    </div>

                    <!-- AI Qualification Data -->
                    <div class="mb-4 border-t border-slate-100 pt-3">
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">AI Qualification</h4>
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between items-center bg-slate-50 border border-slate-100 p-2 rounded-lg">
                                <span class="text-slate-500 font-semibold">Lead Score:</span>
                                <span class="font-extrabold {{ $activeLead->lead_score > 70 ? 'text-emerald-650' : 'text-slate-800' }}">{{ $activeLead->lead_score }}/100</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 font-semibold">Product interest:</span>
                                <span class="font-bold text-slate-800">{{ $activeLead->req_product ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 font-semibold">Budget:</span>
                                <span class="font-bold text-slate-800">{{ $activeLead->req_budget ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 font-semibold">Timeline:</span>
                                <span class="font-bold text-slate-800">{{ $activeLead->req_timeline ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Attribution Data --}}
                    <div class="mb-4 border-t border-slate-100 pt-3">
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">📊 Attribution</h4>
                        <div class="space-y-1.5 text-xs bg-slate-50/50 p-2.5 rounded-xl border border-slate-150/40">
                            @php
                                $attrRows = [
                                    ['Source', $activeLead->source ?? 'Organic WhatsApp'],
                                    ['Campaign', $activeLead->campaign_name ?? $activeLead->utm_campaign ?? null],
                                    ['Ad Name', $activeLead->ad_name ?? null],
                                    ['UTM Source', $activeLead->utm_source ?? null],
                                    ['UTM Medium', $activeLead->utm_medium ?? null],
                                    ['CTWA Click ID', $activeLead->ctwa_clid ? \Illuminate\Support\Str::limit($activeLead->ctwa_clid, 18) : null],
                                ];
                            @endphp
                            @foreach($attrRows as [$label, $val])
                                @if($val)
                                <div class="flex justify-between items-start gap-2">
                                    <span class="text-slate-400 font-semibold whitespace-nowrap">{{ $label }}:</span>
                                    <span class="font-bold text-slate-800 text-right break-all">{{ $val }}</span>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Deal Value -->
                    <div class="mb-4 border-t border-slate-100 pt-3">
                         <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Expected Deal Value ({{ $activeLead->business->currency ?? 'INR' }})</label>
                         <input type="number" wire:model.lazy="activeLead.expected_value" class="w-full border border-slate-200 rounded-lg p-2 text-xs outline-none text-slate-800 font-bold" placeholder="e.g. 5000">
                    </div>

                    {{-- Auto Engage per-conversation toggle --}}
                    <div class="border-t border-slate-100 pt-3">
                        <div class="bg-violet-50 border border-violet-200/70 p-3 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-extrabold text-violet-800 flex items-center space-x-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        <span>Auto Engage</span>
                                    </span>
                                    <span class="text-[10px] text-violet-600 font-medium mt-0.5 block">Window nudge for this chat</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox"
                                           wire:model="activeConversation.auto_engage_enabled"
                                           wire:change="saveConversationAutoEngage"
                                           class="sr-only peer">
                                    <div class="w-8 h-4.5 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-violet-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="border-t border-slate-100 pt-4 text-center">
                    <div class="text-slate-400 text-xs leading-relaxed font-semibold bg-slate-50 border border-slate-200/60 p-3 rounded-xl mb-3">
                        No active sales deal attached to this contact yet.
                    </div>
                    <button wire:click="createLeadForContact" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl transition text-xs font-extrabold uppercase tracking-wider shadow-sm cursor-pointer flex items-center justify-center space-x-1.5">
                        <span>🎯</span>
                        <span>Convert to Pipeline Lead</span>
                    </button>
                </div>
            @endif
        @else
            <div class="text-slate-400 text-xs font-semibold text-center py-6">Select a conversation to view details.</div>
        @endif
    </div>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- Approved Meta Template Picker Modal                                 -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    @if($showTemplateModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full flex flex-col max-h-[90vh] overflow-hidden border border-slate-200">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/80 flex items-center justify-between">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm shadow-xs">
                        ⚡
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-slate-800">Approved Meta Templates</h3>
                        <p class="text-[11px] text-slate-500 font-medium">Select a Meta-approved message template to send to this contact</p>
                    </div>
                </div>
                <button wire:click="closeTemplateModal" class="text-slate-400 hover:text-slate-600 font-extrabold text-lg p-1.5 rounded-lg hover:bg-slate-200/60 transition cursor-pointer">&times;</button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 flex-1 overflow-y-auto grid grid-cols-1 md:grid-cols-12 gap-6 min-h-0">
                
                <!-- Left Side: Template Selector List -->
                <div class="md:col-span-5 flex flex-col space-y-3">
                    <div class="relative">
                        <input type="text" wire:model.live="templateSearch" placeholder="Search templates..." class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>

                    <div class="space-y-2 max-h-[360px] overflow-y-auto custom-scrollbar pr-1">
                        @php
                            $filteredTemplates = collect($approvedTemplates)->filter(function($t) use ($templateSearch) {
                                if (empty($templateSearch)) return true;
                                return str_contains(strtolower($t['name']), strtolower($templateSearch)) || str_contains(strtolower($t['category'] ?? ''), strtolower($templateSearch));
                            });
                        @endphp

                        @forelse($filteredTemplates as $tmpl)
                            <div wire:click="selectTemplateForModal('{{ $tmpl['name'] }}')"
                                 class="p-3 rounded-xl border transition cursor-pointer text-left {{ $selectedTemplateName === $tmpl['name'] ? 'bg-emerald-50/70 border-emerald-500 shadow-xs' : 'bg-slate-50/60 hover:bg-slate-100 border-slate-200/80' }}">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-extrabold text-xs text-slate-800 truncate">{{ $tmpl['name'] }}</span>
                                    <span class="text-[9px] font-extrabold px-2 py-0.5 rounded-full {{ ($tmpl['category'] ?? '') === 'MARKETING' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ $tmpl['category'] ?? 'UTILITY' }}
                                    </span>
                                </div>
                                <div class="text-[10px] text-slate-500 font-medium truncate">
                                    Lang: {{ $tmpl['language'] ?? 'en_US' }} • Status: APPROVED
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-xs text-slate-400 py-6 font-semibold">No approved templates found.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Right Side: Template Preview & Dynamic Variables -->
                <div class="md:col-span-7 bg-slate-50/80 border border-slate-200/80 rounded-xl p-4 flex flex-col justify-between">
                    <div>
                        <h4 class="text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-3 flex items-center justify-between">
                            <span>Message Preview</span>
                            <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded border border-emerald-200">Meta Approved</span>
                        </h4>

                        @if(count($templateVars) > 0)
                            <!-- Dynamic Variable Controls -->
                            <div class="mb-4 bg-white border border-slate-200 rounded-xl p-3 space-y-2.5 shadow-xs">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Template Variables</label>
                                @foreach($templateVars as $varNum => $varVal)
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs font-bold font-mono text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-1 rounded whitespace-nowrap text-[11px]">
                                            &#123;&#123;{{ $varNum }}&#125;&#125;
                                        </span>
                                        <input type="text" wire:model.live="templateVars.{{ $varNum }}"
                                               placeholder="Enter value for {{ $varNum }}"
                                               class="flex-1 border border-slate-200 rounded-lg px-2.5 py-1 text-xs text-slate-800 font-semibold focus:ring-1 focus:ring-emerald-500 outline-none">
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Message Card Preview -->
                        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs text-xs space-y-2 font-sans leading-relaxed">
                            @if($templatePreviewText)
                                <div class="whitespace-pre-line text-slate-800 font-medium">{{ $templatePreviewText }}</div>
                            @else
                                <div class="text-slate-400 italic text-center py-4">Select a template to view preview</div>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4 pt-3 border-t border-slate-200 flex items-center justify-end space-x-2">
                        <button type="button" wire:click="insertTemplateToInput" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition cursor-pointer">
                            Insert Text Only
                        </button>
                        <button type="button" wire:click="sendSelectedTemplate" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                            <span>⚡ Send Approved Template</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endif
</div>

