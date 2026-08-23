<div class="p-8 max-w-7xl mx-auto" x-data="{ showForm: @entangle('showForm') }">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Distributors</h1>
            <p class="text-slate-500 text-sm mt-1">Manage your reseller / channel partners.</p>
        </div>
        <button wire:click="openCreate"
                class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl transition shadow-lg">
            + New Distributor
        </button>
    </div>

    @if($successMsg)
    <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-semibold px-4 py-3 rounded-xl mb-5 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        {{ $successMsg }}
    </div>
    @endif

    {{-- Search & Filter --}}
    <div class="flex gap-3 mb-6">
        <div class="relative flex-1 max-w-sm">
            <svg class="w-4 h-4 text-slate-500 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search name or email…"
                   class="w-full pl-9 pr-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-indigo-500 transition">
        </div>
        <select wire:model.live="filterStatus" class="bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-slate-300 focus:outline-none focus:border-indigo-500 transition">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="bg-[#0d1220] border border-white/6 rounded-2xl overflow-hidden mb-6">
        <table class="w-full text-sm">
            <thead class="border-b border-white/5">
                <tr class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                    <th class="px-6 py-3 text-left">Distributor</th>
                    <th class="px-6 py-3 text-left">Contact</th>
                    <th class="px-6 py-3 text-center">Businesses</th>
                    <th class="px-6 py-3 text-center">Commission</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/4">
                @forelse($distributors as $d)
                <tr class="hover:bg-white/2 transition group">
                    <td class="px-6 py-4">
                        <div class="font-bold text-white">{{ $d->name }}</div>
                        <div class="text-xs text-slate-500">{{ $d->company }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-slate-300 text-xs">{{ $d->email }}</div>
                        <div class="text-slate-500 text-xs">{{ $d->phone }}</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-white font-extrabold text-lg">{{ $d->businesses_count }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-emerald-400 font-bold">{{ $d->commission_pct }}%</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <button wire:click="toggleStatus({{ $d->id }})"
                                class="text-[10px] font-bold px-2.5 py-1 rounded-lg uppercase tracking-wider transition {{ $d->status === 'active' ? 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20' : 'bg-red-500/10 text-red-400 hover:bg-red-500/20' }}">
                            {{ $d->status }}
                        </button>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center gap-2 justify-end opacity-0 group-hover:opacity-100 transition">
                            <button wire:click="openEdit({{ $d->id }})" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 bg-indigo-500/10 px-3 py-1.5 rounded-lg transition">Edit</button>
                            <button wire:click="delete({{ $d->id }})" onclick="return confirm('Delete this distributor?')" class="text-xs font-bold text-red-400 hover:text-red-300 bg-red-500/10 px-3 py-1.5 rounded-lg transition">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">No distributors found. Add your first one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $distributors->links() }}</div>

    {{-- Slide-Over Form --}}
    <div x-show="showForm"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40" style="display:none"
         @click="$wire.showForm = false"></div>

    <div x-show="showForm"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
         class="fixed top-0 right-0 h-full w-[440px] bg-[#0d1220] border-l border-white/8 z-50 flex flex-col shadow-2xl" style="display:none">

        <div class="px-6 py-5 border-b border-white/8 bg-gradient-to-r from-indigo-600/20 to-violet-600/20 flex items-center justify-between">
            <h2 class="text-white font-extrabold text-lg">{{ $editingId ? 'Edit Distributor' : 'New Distributor' }}</h2>
            <button wire:click="$set('showForm', false)" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-6 space-y-4">
            @foreach(['name'=>'Name *','email'=>'Email *','phone'=>'Phone','company'=>'Company','country'=>'Country'] as $field=>$label)
            <div>
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">{{ $label }}</label>
                <input type="{{ $field === 'email' ? 'email' : 'text' }}" wire:model="form.{{ $field }}"
                       class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                @error("form.$field")<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            @endforeach

            <div>
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Commission %</label>
                <input type="number" wire:model="form.commission_pct" min="0" max="100" step="0.5"
                       class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
            </div>

            <div>
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Status</label>
                <select wire:model="form.status" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>

            <div>
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Notes</label>
                <textarea wire:model="form.notes" rows="3" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition resize-none"></textarea>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-white/8 flex gap-3">
            <button wire:click="$set('showForm', false)" class="flex-1 border border-white/10 text-slate-300 font-bold py-2.5 rounded-xl hover:bg-white/5 transition text-sm">Cancel</button>
            <button wire:click="save" wire:loading.attr="disabled" class="flex-1 bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-bold py-2.5 rounded-xl transition text-sm flex items-center justify-center gap-2">
                <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save Changes' : 'Create Distributor' }}</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </div>
</div>
