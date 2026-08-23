<x-layouts.app>
    <div class="min-h-[80vh] flex items-center justify-center px-4">
        <div class="max-w-md w-full text-center space-y-6 bg-[#0d1220] border border-white/8 rounded-2xl p-8 shadow-2xl">
            
            {{-- Lock Icon --}}
            <div class="w-16 h-16 rounded-full bg-indigo-500/10 text-indigo-400 flex items-center justify-center mx-auto shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>

            {{-- Text --}}
            <div>
                <h2 class="text-2xl font-extrabold text-white">Feature Locked</h2>
                <p class="text-indigo-400 text-sm font-semibold mt-1">{{ request('feature', 'Premium Feature') }}</p>
                <p class="text-slate-400 text-sm mt-4 leading-relaxed">
                    This feature is not included in your current subscription plan. Upgrade your plan to get instant access.
                </p>
            </div>

            {{-- Action buttons --}}
            <div class="pt-4 space-y-3">
                <a href="{{ route('billing.renew') }}" 
                   class="block w-full bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-indigo-900/30 text-sm">
                    ⚡ Upgrade Plan Now
                </a>
                <a href="/dashboard" 
                   class="block w-full border border-white/10 text-slate-350 hover:text-white font-bold py-3 rounded-xl hover:bg-white/5 transition text-sm">
                    Back to Dashboard
                </a>
            </div>

        </div>
    </div>
</x-layouts.app>
