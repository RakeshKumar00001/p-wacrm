<form wire:submit.prevent="saveContact" class="space-y-6">

    {{-- Section: Identity --}}
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">👤 Identity</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">Full Name</label>
                <input wire:model="form.name" type="text" placeholder="Jane Smith"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">Phone <span class="text-red-500">*</span></label>
                <input wire:model="form.phone" type="text" placeholder="+919876543210"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                @error('form.phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">Email</label>
                <input wire:model="form.email" type="email" placeholder="jane@example.com"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                @error('form.email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">WhatsApp Number</label>
                <input wire:model="form.whatsapp_number" type="text" placeholder="+919876543210 (if different)"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">Birthday</label>
                <input wire:model="form.birthday" type="date"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">Status</label>
                <select wire:model="form.status" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="blocked">Blocked</option>
                </select>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-2">
            <input wire:model="form.do_not_disturb" type="checkbox" id="dnd_check" class="rounded text-red-600 h-4 w-4 border-slate-300 focus:ring-red-500">
            <label for="dnd_check" class="text-sm font-semibold text-slate-700 cursor-pointer">🔕 Do Not Disturb (DND)</label>
        </div>
    </div>

    {{-- Section: Company --}}
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">🏢 Company & Role</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">Company Name</label>
                <input wire:model="form.company" type="text" placeholder="Acme Corp"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">Designation / Job Title</label>
                <input wire:model="form.designation" type="text" placeholder="CEO, Purchase Manager…"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="col-span-2">
                <label class="text-xs font-semibold text-slate-600 block mb-1">Website</label>
                <input wire:model="form.website" type="url" placeholder="https://acme.com"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
    </div>

    {{-- Section: Location --}}
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">📍 Location</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">City</label>
                <input wire:model="form.city" type="text" placeholder="Mumbai"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">State</label>
                <input wire:model="form.state" type="text" placeholder="Maharashtra"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">Country</label>
                <input wire:model="form.country" type="text" placeholder="India"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="col-span-3">
                <label class="text-xs font-semibold text-slate-600 block mb-1">Full Address</label>
                <textarea wire:model="form.address" rows="2" placeholder="Street, Area, Landmark…"
                          class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
            </div>
        </div>
    </div>

    {{-- Section: Social --}}
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">🔗 Social Profiles</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">LinkedIn URL</label>
                <input wire:model="form.linkedin_url" type="url" placeholder="https://linkedin.com/in/jane"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">Instagram Handle</label>
                <input wire:model="form.instagram_handle" type="text" placeholder="jane.smith (without @)"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
    </div>

    {{-- Section: Notes --}}
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <h3 class="text-xs font-bold text-amber-600 uppercase tracking-widest mb-3">📝 Internal Notes</h3>
        <textarea wire:model="form.notes" rows="3" placeholder="Anything relevant — preferences, objections, referral details, follow-up context…"
                  class="w-full border border-amber-200 bg-white rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-amber-400 resize-none"></textarea>
    </div>

    {{-- Actions --}}
    <div class="flex gap-3 pb-4">
        <button type="submit"
                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl text-sm font-bold transition shadow-sm">
            <span wire:loading.remove>💾 Save Contact</span>
            <span wire:loading>Saving…</span>
        </button>
        <button type="button" wire:click="cancelEdit"
                class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-bold transition">
            Cancel
        </button>
    </div>

</form>
