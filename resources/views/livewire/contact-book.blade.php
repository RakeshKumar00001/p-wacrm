<div class="flex h-screen bg-[#f8fafc] overflow-hidden font-sans" x-data="{ showImportModal: $wire.entangle('showImportModal') }">

    {{-- LEFT: Contact List --}}
    <div class="w-96 bg-white border-r border-slate-200 flex flex-col flex-shrink-0">

        {{-- Header --}}
        <div class="p-5 border-b border-slate-100 bg-slate-50/60">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900">Contacts</h1>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $stats['total'] }} total contacts</p>
                </div>
                <div class="flex items-center gap-2">
                    {{-- Export --}}
                    <button wire:click="exportLeads"
                            wire:loading.attr="disabled"
                            class="flex items-center gap-1.5 border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold px-3 py-2 rounded-xl transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span wire:loading.remove wire:target="exportLeads">Export CSV</span>
                        <span wire:loading wire:target="exportLeads">Exporting…</span>
                    </button>
                    {{-- Import --}}
                    <button @click="showImportModal = true"
                            class="flex items-center gap-1.5 border border-violet-300 bg-violet-50 hover:bg-violet-100 text-violet-700 text-xs font-bold px-3 py-2 rounded-xl transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l4-4m0 0l4 4m-4-4v12"/></svg>
                        Import CSV
                    </button>
                    {{-- New Contact --}}
                    <button wire:click="startCreate"
                            class="flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-3.5 py-2 rounded-xl transition shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        New Contact
                    </button>
                </div>
            </div>

            {{-- Stats Row --}}
            <div class="grid grid-cols-3 gap-2 mb-4">
                <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-2.5 text-center">
                    <div class="text-lg font-extrabold text-emerald-700">{{ $stats['active'] }}</div>
                    <div class="text-[9px] font-bold text-emerald-500 uppercase tracking-wider">Active</div>
                </div>
                <div class="bg-red-50 border border-red-100 rounded-xl p-2.5 text-center">
                    <div class="text-lg font-extrabold text-red-600">{{ $stats['dnd'] }}</div>
                    <div class="text-[9px] font-bold text-red-400 uppercase tracking-wider">DND</div>
                </div>
                <div class="bg-amber-50 border border-amber-100 rounded-xl p-2.5 text-center">
                    <div class="text-lg font-extrabold text-amber-600">{{ $stats['no_lead'] }}</div>
                    <div class="text-[9px] font-bold text-amber-500 uppercase tracking-wider">No Lead</div>
                </div>
            </div>

            {{-- Search --}}
            <div class="relative mb-3">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search name, phone, email, company..."
                       class="w-full pl-9 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
            </div>

            {{-- Filters --}}
            <div class="flex gap-2">
                <select wire:model.live="filterStatus" class="flex-1 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 outline-none bg-white">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="blocked">Blocked</option>
                </select>
                <select wire:model.live="sortBy" class="flex-1 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-600 outline-none bg-white">
                    <option value="created_at">Newest</option>
                    <option value="name">Name A-Z</option>
                    <option value="last_contacted_at">Last Contacted</option>
                    <option value="company">Company</option>
                </select>
            </div>
        </div>

        {{-- Contact List --}}
        <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
            @forelse($contacts as $contact)
            <div wire:click="selectContact({{ $contact->id }})"
                 class="px-4 py-3.5 cursor-pointer hover:bg-slate-50 transition-all duration-100 relative
                 {{ $selectedContactId === $contact->id ? 'bg-indigo-50/50 border-l-4 border-indigo-600' : 'border-l-4 border-transparent' }}">

                <div class="flex items-start gap-3">
                    {{-- Avatar --}}
                    <div class="w-10 h-10 rounded-full {{ $contact->avatar_color }} text-white flex items-center justify-center text-sm font-extrabold flex-shrink-0 shadow-sm">
                        {{ $contact->initials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-slate-800 text-sm truncate">{{ $contact->name ?? $contact->phone }}</span>
                            @if($contact->do_not_disturb)
                                <span class="text-[9px] font-bold text-red-600 bg-red-50 px-1.5 py-0.5 rounded flex-shrink-0">DND</span>
                            @endif
                        </div>
                        <div class="text-xs text-slate-400 font-mono mt-0.5">{{ $contact->phone }}</div>
                        @if($contact->company)
                            <div class="text-xs text-slate-500 mt-0.5 font-medium truncate">🏢 {{ $contact->company }}{{ $contact->designation ? ' · '.$contact->designation : '' }}</div>
                        @endif
                        <div class="flex items-center gap-1.5 mt-1.5">
                            @php $lead = $contact->leads->first(); @endphp
                            @if($lead)
                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded"
                                      style="background-color: {{ $lead->stage?->color ?? '#E2E8F0' }}20; color: {{ $lead->stage?->color ?? '#64748B' }};">
                                    {{ $lead->stage?->name ?? 'No Stage' }}
                                </span>
                            @endif
                            @if($contact->city)
                                <span class="text-[9px] text-slate-400">📍 {{ $contact->city }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-12 text-center">
                <div class="text-4xl mb-3">👥</div>
                <p class="text-sm text-slate-400 font-semibold">No contacts found</p>
                <button wire:click="startCreate" class="mt-3 text-xs text-indigo-600 hover:underline font-bold">+ Add your first contact</button>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="p-3 border-t border-slate-100 bg-white">
            {{ $contacts->links() }}
        </div>
    </div>

    {{-- RIGHT: Detail / Edit Panel --}}
    <div class="flex-1 overflow-y-auto">

        @if ($statusMsg)
        <div class="m-4 p-3 rounded-xl text-sm font-semibold flex items-center gap-2
            {{ $statusType === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-blue-50 border border-blue-200 text-blue-800' }}">
            <span>{{ $statusType === 'success' ? '✅' : 'ℹ️' }}</span>
            <span>{{ $statusMsg }}</span>
            <button wire:click="$set('statusMsg','')" class="ml-auto text-gray-400">✕</button>
        </div>
        @endif

        {{-- Create Form --}}
        @if($isCreating)
        <div class="max-w-3xl mx-auto p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-extrabold text-slate-900">New Contact</h2>
                <button wire:click="cancelEdit" class="text-slate-400 hover:text-slate-700 text-sm font-bold">✕ Cancel</button>
            </div>
            @include('livewire.partials.contact-form')
        </div>

        {{-- Edit Form --}}
        @elseif($isEditing && $selectedContact)
        <div class="max-w-3xl mx-auto p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-extrabold text-slate-900">Edit Contact</h2>
                <button wire:click="cancelEdit" class="text-slate-400 hover:text-slate-700 text-sm font-bold">✕ Cancel</button>
            </div>
            @include('livewire.partials.contact-form')
        </div>

        {{-- Contact Detail View --}}
        @elseif($selectedContact)
        <div class="max-w-4xl mx-auto p-6 space-y-6">

            {{-- Profile Header --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="h-20 bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-600"></div>
                <div class="px-6 pb-6">
                    <div class="flex items-end justify-between -mt-10 mb-4">
                        <div class="w-20 h-20 rounded-2xl {{ $selectedContact->avatar_color }} text-white flex items-center justify-center text-2xl font-extrabold shadow-lg border-4 border-white">
                            {{ $selectedContact->initials }}
                        </div>
                        <div class="flex items-center gap-2 mt-10">
                            @if($selectedContact->do_not_disturb)
                            <span class="text-xs font-bold text-red-600 bg-red-50 border border-red-200 px-2.5 py-1 rounded-lg">🔕 DND</span>
                            @endif
                            <span class="text-xs font-bold px-2.5 py-1 rounded-lg border
                                {{ $selectedContact->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                   ($selectedContact->status === 'blocked' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-slate-100 text-slate-600 border-slate-200') }}">
                                {{ ucfirst($selectedContact->status ?? 'active') }}
                            </span>
                            <button wire:click="startEdit"
                                    class="text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl transition shadow-sm">
                                ✏️ Edit
                            </button>
                            <button wire:click="deleteContact"
                                    onclick="return confirm('Delete this contact and all their data?')"
                                    class="text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 px-3 py-2 rounded-xl transition">
                                🗑️
                            </button>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-2xl font-extrabold text-slate-900">{{ $selectedContact->name ?? $selectedContact->phone }}</h2>
                        @if($selectedContact->designation || $selectedContact->company)
                        <p class="text-sm text-slate-500 mt-0.5">
                            {{ $selectedContact->designation }}{{ $selectedContact->designation && $selectedContact->company ? ' at ' : '' }}{{ $selectedContact->company }}
                        </p>
                        @endif
                    </div>

                    {{-- Quick Action Buttons --}}
                    <div class="flex gap-2 mt-4">
                        <button wire:click="startChatWithContact({{ $selectedContact->id }})" class="flex items-center gap-1.5 text-xs font-bold bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl transition shadow-sm cursor-pointer">
                            💬 Open Chat
                        </button>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $selectedContact->phone) }}" target="_blank"
                           class="flex items-center gap-1.5 text-xs font-bold bg-white border border-green-300 text-green-700 hover:bg-green-50 px-4 py-2 rounded-xl transition">
                            📱 WhatsApp
                        </a>
                        @if($selectedContact->email)
                        <a href="mailto:{{ $selectedContact->email }}"
                           class="flex items-center gap-1.5 text-xs font-bold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-xl transition">
                            ✉️ Email
                        </a>
                        @endif
                        @if($selectedContact->linkedin_url)
                        <a href="{{ $selectedContact->linkedin_url }}" target="_blank"
                           class="flex items-center gap-1.5 text-xs font-bold bg-white border border-blue-200 text-blue-700 hover:bg-blue-50 px-4 py-2 rounded-xl transition">
                            🔗 LinkedIn
                        </a>
                        @endif
                        <button wire:click="toggleDnd"
                                class="flex items-center gap-1.5 text-xs font-bold bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 px-3 py-2 rounded-xl transition ml-auto">
                            {{ $selectedContact->do_not_disturb ? '🔔 Remove DND' : '🔕 Set DND' }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- Info Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                {{-- Contact Details --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Contact Details</h3>
                    <div class="space-y-3">
                        @foreach([
                            ['📞', 'Phone',      $selectedContact->phone],
                            ['💬', 'WhatsApp',   $selectedContact->whatsapp_number],
                            ['✉️', 'Email',      $selectedContact->email],
                            ['🏢', 'Company',    $selectedContact->company],
                            ['💼', 'Designation',$selectedContact->designation],
                            ['🌐', 'Website',    $selectedContact->website],
                            ['🎂', 'Birthday',   $selectedContact->birthday?->format('d M Y')],
                        ] as [$icon, $label, $val])
                        @if($val)
                        <div class="flex items-start gap-3 text-sm">
                            <span class="text-base w-6 flex-shrink-0">{{ $icon }}</span>
                            <div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $label }}</div>
                                <div class="font-semibold text-slate-800 mt-0.5">{{ $val }}</div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>

                {{-- Location & Online --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Location & Social</h3>
                    <div class="space-y-3">
                        @foreach([
                            ['📍', 'City',          $selectedContact->city],
                            ['🗺️', 'State',         $selectedContact->state],
                            ['🌍', 'Country',        $selectedContact->country],
                            ['🏠', 'Address',        $selectedContact->address],
                            ['🔗', 'LinkedIn',       $selectedContact->linkedin_url],
                            ['📸', 'Instagram',      $selectedContact->instagram_handle ? '@'.$selectedContact->instagram_handle : null],
                        ] as [$icon, $label, $val])
                        @if($val)
                        <div class="flex items-start gap-3 text-sm">
                            <span class="text-base w-6 flex-shrink-0">{{ $icon }}</span>
                            <div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $label }}</div>
                                <div class="font-semibold text-slate-800 mt-0.5 break-all">{{ $val }}</div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>

                {{-- Lead / Sales Info --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Sales Pipeline</h3>
                    @if($selectedLead)
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Stage</span>
                            <span class="font-bold text-slate-800 px-2 py-0.5 rounded text-xs"
                                  style="background:{{ $selectedLead->stage?->color ?? '#E2E8F0' }}20;color:{{ $selectedLead->stage?->color ?? '#64748B' }}">
                                {{ $selectedLead->stage?->name ?? '—' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Lead Score</span>
                            <span class="font-extrabold {{ $selectedLead->lead_score > 70 ? 'text-emerald-600' : 'text-slate-700' }}">{{ $selectedLead->lead_score }}/100</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Deal Value</span>
                            <span class="font-extrabold text-slate-800">₹{{ number_format($selectedLead->expected_value ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Source</span>
                            <span class="font-bold text-slate-700 text-xs">{{ $selectedLead->source ?? '—' }}</span>
                        </div>
                        @if($selectedLead->utm_campaign)
                        <div class="flex justify-between">
                            <span class="text-slate-500">Campaign</span>
                            <span class="font-bold text-slate-700 text-xs">{{ $selectedLead->utm_campaign }}</span>
                        </div>
                        @endif
                        @if($selectedLead->campaign_name)
                        <div class="flex justify-between">
                            <span class="text-slate-500">Ad Campaign</span>
                            <span class="font-bold text-slate-700 text-xs">{{ $selectedLead->campaign_name }}</span>
                        </div>
                        @endif
                        @if($selectedLead->req_product)
                        <div class="flex justify-between">
                            <span class="text-slate-500">Interested In</span>
                            <span class="font-bold text-slate-700">{{ $selectedLead->req_product }}</span>
                        </div>
                        @endif
                        @if($selectedLead->req_budget)
                        <div class="flex justify-between">
                            <span class="text-slate-500">Budget</span>
                            <span class="font-bold text-slate-700">{{ $selectedLead->req_budget }}</span>
                        </div>
                        @endif
                        @if($selectedLead->req_timeline)
                        <div class="flex justify-between">
                            <span class="text-slate-500">Timeline</span>
                            <span class="font-bold text-slate-700">{{ $selectedLead->req_timeline }}</span>
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="text-center text-xs text-slate-400 py-4">No lead associated yet.</div>
                    @endif
                </div>

                {{-- Tags --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Tags & Labels</h3>
                    <form wire:submit.prevent="addTag" class="flex gap-2 mb-3">
                        <input wire:model="newTag" type="text" placeholder="Add tag (VIP, Hot, Follow-up…)"
                               class="flex-1 border border-slate-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="submit" class="bg-indigo-600 text-white px-3 py-2 rounded-xl text-xs font-bold hover:bg-indigo-700">Add</button>
                    </form>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse($selectedContact->tags_array as $tag)
                        <span class="inline-flex items-center gap-1 text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 px-2.5 py-1 rounded-lg">
                            {{ $tag }}
                            <button wire:click="removeTag('{{ $tag }}')" class="text-indigo-300 hover:text-indigo-600 font-extrabold leading-none">×</button>
                        </span>
                        @empty
                        <span class="text-xs text-slate-400">No tags yet.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            @if($selectedContact->notes)
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
                <h3 class="text-xs font-bold text-amber-700 uppercase tracking-widest mb-2">📝 Notes</h3>
                <p class="text-sm text-amber-900 leading-relaxed">{{ $selectedContact->notes }}</p>
            </div>
            @endif

            {{-- Activity Timeline --}}
            @if($selectedLead && $selectedLead->activities->isNotEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">📅 Activity Timeline</h3>
                <div class="space-y-3">
                    @foreach($selectedLead->activities->take(8) as $activity)
                    <div class="flex gap-3 text-sm">
                        <div class="w-2 h-2 rounded-full bg-indigo-400 mt-1.5 flex-shrink-0"></div>
                        <div class="flex-1">
                            <p class="text-slate-700 font-medium">{{ $activity->description }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Meta / Timestamps --}}
            <div class="text-xs text-slate-400 flex gap-4 pb-6">
                <span>Created: {{ $selectedContact->created_at->format('d M Y, H:i') }}</span>
                <span>Updated: {{ $selectedContact->updated_at->diffForHumans() }}</span>
                @if($selectedContact->last_contacted_at)
                <span>Last Contact: {{ $selectedContact->last_contacted_at->diffForHumans() }}</span>
                @endif
            </div>

        </div>

        {{-- Empty State --}}
        @else
        <div class="flex flex-col items-center justify-center h-full text-slate-400">
            <div class="text-6xl mb-4">👤</div>
            <p class="text-sm font-semibold mb-2">Select a contact to view details</p>
            <button wire:click="startCreate" class="text-xs text-indigo-600 hover:underline font-bold mt-1">+ Create new contact</button>
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- Import Modal                                                       --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div x-show="showImportModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4"
         style="display:none;">

        <div @click.outside="showImportModal = false"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">

            {{-- Modal Header --}}
            <div class="px-6 py-5 bg-gradient-to-r from-violet-600 to-indigo-600 flex items-center justify-between">
                <div>
                    <h2 class="text-white font-extrabold text-lg">Import Leads via CSV</h2>
                    <p class="text-violet-200 text-xs mt-0.5">Upload a CSV file to bulk-import contacts & leads</p>
                </div>
                <button @click="showImportModal = false" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 space-y-5">

                {{-- Result banner --}}
                @if($importResult)
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-sm text-emerald-800 font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    {{ $importResult }}
                </div>
                @endif

                {{-- File Drop Zone --}}
                <div x-data="{ dragging: false }"
                     @dragover.prevent="dragging = true"
                     @dragleave.prevent="dragging = false"
                     @drop.prevent="dragging = false"
                     :class="dragging ? 'border-violet-400 bg-violet-50' : 'border-slate-200 bg-slate-50'"
                     class="border-2 border-dashed rounded-xl p-6 text-center transition-colors">
                    <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="text-sm font-semibold text-slate-600 mb-1">Drop your CSV here or</p>
                    <label class="cursor-pointer inline-block">
                        <span class="text-xs font-bold text-violet-600 hover:text-violet-700 underline underline-offset-2">browse file</span>
                        <input type="file" wire:model="importFile" accept=".csv,.txt" class="hidden">
                    </label>
                    <p class="text-[10px] text-slate-400 mt-2">CSV only · Max 5MB</p>
                </div>

                {{-- File selected indicator --}}
                @if($importFile)
                <div class="flex items-center gap-2 bg-violet-50 border border-violet-200 rounded-xl px-4 py-2.5">
                    <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="text-xs font-semibold text-violet-700 truncate">{{ $importFile->getClientOriginalName() }}</span>
                    <span class="text-[10px] text-violet-400 ml-auto">{{ round($importFile->getSize()/1024, 1) }} KB</span>
                </div>
                @endif

                @error('importFile')
                <p class="text-xs text-red-500 font-semibold">{{ $message }}</p>
                @enderror

                {{-- Column Guide --}}
                <div x-data="{ open: false }" class="border border-slate-200 rounded-xl overflow-hidden">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        <span>📋 Expected CSV Column Order</span>
                        <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-collapse class="border-t border-slate-100 bg-slate-50 px-4 py-3">
                        <div class="grid grid-cols-2 gap-1 text-[10px] text-slate-500 font-mono">
                            @foreach(['Name','Phone*','Email','Company','Designation','City','State','Country','Website','Source','Lead Score','Expected Value','Stage','UTM Source','UTM Medium','UTM Campaign','Notes','Tags','Status','Created At'] as $i => $col)
                            <div class="flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded bg-slate-200 text-slate-600 flex items-center justify-center text-[9px] font-extrabold flex-shrink-0">{{ $i+1 }}</span>
                                <span class="{{ $col === 'Phone*' ? 'text-red-600 font-bold' : '' }}">{{ $col }}</span>
                            </div>
                            @endforeach
                        </div>
                        <p class="text-[10px] text-slate-400 mt-2">* Phone is required. Tip: Export first to get the correct format.</p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex gap-3 pt-1">
                    <button @click="showImportModal = false"
                            class="flex-1 border border-slate-200 text-slate-600 font-bold text-sm py-2.5 rounded-xl hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button wire:click="importLeads"
                            wire:loading.attr="disabled"
                            class="flex-1 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white font-bold text-sm py-2.5 rounded-xl transition shadow-sm flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="importLeads">⬆ Upload & Import</span>
                        <span wire:loading wire:target="importLeads" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                            Importing…
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
