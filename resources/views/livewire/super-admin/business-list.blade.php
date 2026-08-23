<div class="p-8 max-w-7xl mx-auto" x-data="{ showForm: @entangle('showCreateForm') }">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Businesses</h1>
            <p class="text-slate-500 text-sm mt-1">All tenants. Click a row to see their team. Super Admin can create businesses directly.</p>
        </div>
        <button wire:click="openCreate"
                class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl transition shadow-lg flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            New Business
        </button>
    </div>

    {{-- Success msg --}}
    @if($successMsg)
    <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-semibold px-4 py-3 rounded-xl mb-5 flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        {{ $successMsg }}
    </div>
    @endif

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <div class="relative flex-1 min-w-48">
            <svg class="w-4 h-4 text-slate-500 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by name or email…"
                   class="w-full pl-9 pr-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500 transition">
        </div>
        <select wire:model.live="filterStatus" class="bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-slate-300 focus:outline-none focus:border-indigo-500 transition">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="trial">Trial</option>
            <option value="suspended">Suspended</option>
        </select>
        <select wire:model.live="filterPlan" class="bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-slate-300 focus:outline-none focus:border-indigo-500 transition">
            <option value="">All Plans</option>
            <option value="starter">Starter</option>
            <option value="growth">Growth</option>
            <option value="enterprise">Enterprise</option>
        </select>
        <select wire:model.live="filterDistributor" class="bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-slate-300 focus:outline-none focus:border-indigo-500 transition">
            <option value="">All Distributors</option>
            <option value="0">Direct (No Distributor)</option>
            @foreach($distributors as $dist)
            <option value="{{ $dist->id }}">{{ $dist->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Table --}}
    <div class="bg-[#0d1220] border border-white/6 rounded-2xl overflow-hidden mb-6">
        <table class="w-full text-sm">
            <thead class="border-b border-white/5">
                <tr class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                    <th class="px-6 py-3 text-left">Business</th>
                    <th class="px-6 py-3 text-left">Distributor</th>
                    <th class="px-6 py-3 text-center">Leads</th>
                    <th class="px-6 py-3 text-center">Plan</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/4">
                @forelse($businesses as $b)
                <tr class="hover:bg-white/2 transition cursor-pointer group {{ $viewingId === $b->id ? 'bg-indigo-500/5' : '' }}"
                    wire:click="viewBusiness({{ $b->id }})">
                    <td class="px-6 py-4">
                        <div class="font-bold text-white">{{ $b->name }}</div>
                        <div class="text-xs text-slate-500">{{ $b->owner_email }}</div>
                    </td>
                    <td class="px-6 py-4 text-slate-400 text-xs">
                        {{ $b->distributor?->name ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-center font-bold text-white">{{ $b->leads_count }}</td>
                    <td class="px-6 py-4 text-center" wire:click.stop>
                        <select wire:change="updatePlan({{ $b->id }}, $event.target.value)"
                                class="text-[10px] font-bold px-2 py-1 rounded-lg bg-[#0d1220] border border-white/10 text-slate-300 focus:outline-none cursor-pointer">
                            @foreach(['starter','growth','enterprise'] as $p)
                            <option value="{{ $p }}" {{ ($b->plan ?? 'starter') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-6 py-4 text-center" wire:click.stop>
                        <select wire:change="updateStatus({{ $b->id }}, $event.target.value)"
                                class="text-[10px] font-bold px-2 py-1 rounded-lg bg-[#0d1220] border border-white/10 focus:outline-none cursor-pointer
                                {{ ($b->status ?? 'active') === 'active' ? 'text-emerald-400' : (($b->status === 'trial') ? 'text-amber-400' : 'text-red-400') }}">
                            @foreach(['active','trial','suspended'] as $s)
                            <option value="{{ $s }}" {{ ($b->status ?? 'active') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button wire:click.stop="viewBusiness({{ $b->id }})"
                                class="text-xs font-bold text-indigo-400 hover:text-indigo-300 bg-indigo-500/10 px-3 py-1.5 rounded-lg transition opacity-0 group-hover:opacity-100">
                            {{ $viewingId === $b->id ? 'Collapse ↑' : 'View Team →' }}
                        </button>
                    </td>
                </tr>

                {{-- Drill-down --}}
                @if($viewingId === $b->id && $viewingBusiness)
                <tr>
                    <td colspan="6" class="px-6 py-5 bg-indigo-950/20 border-b border-white/5">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-white font-extrabold text-base">{{ $viewingBusiness->name }} — Team</h3>
                                <p class="text-slate-500 text-xs mt-0.5">
                                    {{ $viewingUsers->count() }} user(s) ·
                                    {{ $viewingBusiness->leads_count }} leads ·
                                    {{ $viewingBusiness->conversations_count }} conversations
                                </p>
                            </div>
                            <button wire:click="viewBusiness({{ $b->id }})" class="text-slate-500 hover:text-white text-xs transition">✕ Close</button>
                        </div>
                        @if($viewingUsers->isEmpty())
                        <p class="text-slate-500 text-sm">No users in this business yet.</p>
                        @else
                        <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($viewingUsers as $u)
                            <div class="bg-white/3 border border-white/5 rounded-xl p-3 flex flex-col gap-2.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-500 to-violet-600 flex items-center justify-center text-xs font-extrabold text-white flex-shrink-0">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-bold text-white truncate">{{ $u->name }}</div>
                                        <div class="text-xs text-slate-500 truncate">{{ $u->email }}</div>
                                        <span class="text-[9px] font-bold uppercase tracking-wider
                                            {{ $u->role === 'owner' ? 'text-amber-400' : ($u->role === 'manager' ? 'text-blue-400' : 'text-slate-500') }}">
                                            {{ $u->role }}
                                        </span>
                                    </div>
                                </div>
                                {{-- Impersonate Button --}}
                                <a href="{{ route('super-admin.impersonate', $u->id) }}"
                                   onclick="return confirm('Login as {{ addslashes($u->name) }} ({{ addslashes($u->email) }})?')"
                                   class="w-full flex items-center justify-center gap-1.5 text-[10px] font-bold uppercase tracking-wider
                                          bg-indigo-500/10 hover:bg-indigo-500/25 border border-indigo-500/20 hover:border-indigo-400/40
                                          text-indigo-400 hover:text-indigo-300 rounded-lg py-1.5 transition-all duration-150">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                              d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                    </svg>
                                    Login as
                                </a>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </td>
                </tr>
                @endif

                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <div class="text-4xl mb-3">🏬</div>
                        <p class="text-slate-500 text-sm font-semibold">No businesses found.</p>
                        <button wire:click="openCreate" class="mt-3 text-xs font-bold text-indigo-400 hover:text-indigo-300 transition">+ Create your first business</button>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $businesses->links() }}</div>

    {{-- ═══════════════════════════════════ --}}
    {{-- Create Business Slide-Over         --}}
    {{-- ═══════════════════════════════════ --}}

    {{-- Backdrop --}}
    <div x-show="showForm"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40" style="display:none"
         @click="$wire.showCreateForm = false"></div>

    {{-- Drawer --}}
    <div x-show="showForm"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
         class="fixed top-0 right-0 h-full w-[480px] bg-[#0d1220] border-l border-white/8 z-50 flex flex-col shadow-2xl" style="display:none">

        {{-- Drawer Header --}}
        <div class="px-6 py-5 border-b border-white/8 bg-gradient-to-r from-indigo-600/20 to-violet-600/20 flex items-center justify-between">
            <div>
                <h2 class="text-white font-extrabold text-lg">Onboard New Business</h2>
                <p class="text-indigo-300/60 text-xs mt-0.5">Creates business + owner account + default pipeline stages</p>
            </div>
            <button wire:click="$set('showCreateForm', false)" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Form Body --}}
        <div class="flex-1 overflow-y-auto px-6 py-6 space-y-5">

            {{-- Business Info --}}
            <div>
                <div class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                    <span class="w-5 h-5 rounded bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-[9px]">1</span>
                    Business Info
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Business Name *</label>
                        <input type="text" wire:model="form.name" placeholder="e.g. Sharma Motors"
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                        @error('form.name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Plan</label>
                            <select wire:model="form.plan" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                                <option value="starter">Starter</option>
                                <option value="growth">Growth</option>
                                <option value="enterprise">Enterprise</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Status</label>
                            <select wire:model="form.status" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                                <option value="trial">Trial</option>
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Currency</label>
                            <select wire:model="form.currency" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                                <option value="INR">INR ₹</option>
                                <option value="USD">USD $</option>
                                <option value="AED">AED د.إ</option>
                                <option value="GBP">GBP £</option>
                                <option value="EUR">EUR €</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Timezone</label>
                            <select wire:model="form.timezone" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                                <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
                                <option value="Asia/Dubai">Asia/Dubai (GST)</option>
                                <option value="UTC">UTC</option>
                                <option value="America/New_York">America/New_York</option>
                                <option value="Europe/London">Europe/London</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Assign Distributor (optional)</label>
                        <select wire:model="form.distributor_id" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                            <option value="">— Direct (No Distributor) —</option>
                            @foreach($distributors as $dist)
                            <option value="{{ $dist->id }}">{{ $dist->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="border-t border-white/5"></div>

            {{-- Owner Account --}}
            <div>
                <div class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                    <span class="w-5 h-5 rounded bg-violet-500/20 text-violet-400 flex items-center justify-center text-[9px]">2</span>
                    Owner / Admin Account
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Full Name *</label>
                        <input type="text" wire:model="form.owner_name" placeholder="e.g. Rajesh Sharma"
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                        @error('form.owner_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Email *</label>
                        <input type="email" wire:model="form.owner_email" placeholder="owner@business.com"
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                        @error('form.owner_email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Phone</label>
                        <input type="text" wire:model="form.owner_phone" placeholder="+91..."
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                    </div>
                    <div>
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Login Password *</label>
                        <input type="password" wire:model="form.owner_password" placeholder="Min 8 characters"
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                        @error('form.owner_password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- What gets created note --}}
            <div class="bg-indigo-500/5 border border-indigo-500/10 rounded-xl p-4">
                <p class="text-xs font-bold text-indigo-400 mb-2">What gets created automatically:</p>
                <ul class="text-xs text-slate-400 space-y-1">
                    <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Business tenant account</li>
                    <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Owner login (role: owner)</li>
                    <li class="flex items-center gap-2"><span class="text-emerald-400">✓</span> 6 default pipeline stages (New Lead → Lost)</li>
                </ul>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-white/8 flex gap-3">
            <button wire:click="$set('showCreateForm', false)" class="flex-1 border border-white/10 text-slate-300 font-bold py-2.5 rounded-xl hover:bg-white/5 transition text-sm">
                Cancel
            </button>
            <button wire:click="createBusiness" wire:loading.attr="disabled" wire:loading.class="opacity-60"
                    class="flex-1 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-bold py-2.5 rounded-xl transition text-sm flex items-center justify-center gap-2">
                <span wire:loading.remove wire:target="createBusiness">🏬 Create Business</span>
                <span wire:loading wire:target="createBusiness" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                    Creating…
                </span>
            </button>
        </div>
    </div>

</div>
