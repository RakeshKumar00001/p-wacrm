<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-[#06080f]">
    <div class="max-w-md w-full space-y-8">
        {{-- Header / Logo --}}
        <div class="text-center">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-500 to-violet-600 flex items-center justify-center text-white font-extrabold text-lg mx-auto mb-4 shadow-lg shadow-indigo-500/25">WA</div>
            <h2 class="text-3xl font-extrabold text-white">Create your account</h2>
            <p class="text-slate-400 text-sm mt-1">Get started with WACRM in minutes</p>
        </div>

        {{-- Register Card --}}
        <div class="bg-[#0d1220] border border-white/8 rounded-2xl p-8 shadow-2xl">
            <form wire:submit.prevent="register" class="space-y-5">
                
                {{-- Business Section --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Business / Company Name *</label>
                        <input type="text" wire:model="form.business_name" required
                               class="w-full bg-white/5 border {{ $errors->has('form.business_name') ? 'border-red-500/50' : 'border-white/10' }} rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition placeholder-slate-600"
                               placeholder="e.g. Acme Solutions">
                        @error('form.business_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Select Plan</label>
                            <select wire:model="form.plan"
                                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500 transition">
                                <option value="starter">Starter (₹4,999/mo)</option>
                                <option value="growth">Growth (₹12,999/mo)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Signup Option</label>
                            <select wire:model="form.signup_type"
                                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500 transition">
                                <option value="trial">7-Day Free Trial</option>
                                <option value="pay">Pay Now (30-Day)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="border-t border-white/5 my-4"></div>

                {{-- Owner Details Section --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Your Full Name *</label>
                        <input type="text" wire:model="form.owner_name" required
                               class="w-full bg-white/5 border {{ $errors->has('form.owner_name') ? 'border-red-500/50' : 'border-white/10' }} rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition placeholder-slate-650"
                               placeholder="e.g. Rajesh Kumar">
                        @error('form.owner_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email Address *</label>
                        <input type="email" wire:model="form.owner_email" required
                               class="w-full bg-white/5 border {{ $errors->has('form.owner_email') ? 'border-red-500/50' : 'border-white/10' }} rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition placeholder-slate-650"
                               placeholder="owner@business.com">
                        @error('form.owner_email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Phone Number</label>
                        <input type="text" wire:model="form.owner_phone"
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition placeholder-slate-650"
                               placeholder="e.g. +91 99999 88888">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Password *</label>
                        <input type="password" wire:model="form.password" required
                               class="w-full bg-white/5 border {{ $errors->has('form.password') ? 'border-red-500/50' : 'border-white/10' }} rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition placeholder-slate-600"
                               placeholder="Min 8 characters">
                        @error('form.password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" wire:loading.attr="disabled"
                            class="w-full bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-indigo-900/30 text-sm flex items-center justify-center gap-2">
                        <span wire:loading.remove>Create Account & Start →</span>
                        <span wire:loading class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                            Creating account…
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <p class="text-center text-sm text-slate-400">
            Already have an account? <a href="/login" class="text-indigo-400 hover:text-indigo-300 font-semibold transition">Login here</a>
        </p>
    </div>
</div>
