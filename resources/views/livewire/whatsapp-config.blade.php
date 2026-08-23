<div class="p-8 max-w-4xl mx-auto font-sans">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">WhatsApp Cloud API Configuration</h1>
        <p class="text-gray-600 mt-1">Connect your Meta WhatsApp Business Account (WABA) credentials to send and receive real-time messages.</p>
    </div>

    @if($testStatusMessage)
        <div class="mb-6 p-4 rounded-lg flex items-center justify-between {{ $testStatusType === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' }}">
            <span>{{ $testStatusMessage }}</span>
            <button wire:click="$set('testStatusMessage', null)" class="text-sm font-bold opacity-75 hover:opacity-100">&times;</button>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Settings Form -->
        <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-800 border-b border-gray-100 pb-4 mb-6">API Credentials</h2>

            <form wire:submit.prevent="saveSettings" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">WhatsApp Business Account (WABA) ID</label>
                    <input type="text" wire:model="wabaId" placeholder="e.g. 10928374659201" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    <p class="text-xs text-gray-500 mt-1">Found in Meta Business Manager -> WhatsApp Accounts.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Phone Number ID</label>
                    <input type="text" wire:model="phoneNumberId" placeholder="e.g. 10492837492817" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    <p class="text-xs text-gray-500 mt-1">Found under Meta Developer Portal -> WhatsApp -> API Setup.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">WhatsApp Access Token (Temporary or Permanent)</label>
                    <textarea wire:model="whatsappAccessToken" rows="3" placeholder="EAAG..." class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm font-mono focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Accepts Meta <b>24-hour Temporary Token</b> (for testing) or <b>Permanent System User Token</b> (for production).</p>
                </div>

                <div class="pt-4 flex items-center space-x-4">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2.5 rounded-lg shadow-sm transition">
                        Save WhatsApp Credentials
                    </button>

                    <button type="button" wire:click="testConnection" class="bg-slate-800 hover:bg-slate-900 text-white font-semibold px-5 py-2.5 rounded-lg shadow-sm transition flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        <span>Test Connection</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Webhook Guide Sidebar -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3 mb-4">Webhook Callback Configuration</h3>
                
                <div class="space-y-4 text-sm">
                    <div>
                        <span class="block font-semibold text-gray-600 mb-1">Callback URL</span>
                        <div class="bg-gray-100 p-2.5 rounded text-xs font-mono select-all break-all border border-gray-200">
                            {{ $webhookUrl }}
                        </div>
                    </div>

                    <div>
                        <span class="block font-semibold text-gray-600 mb-1">Verify Token</span>
                        <div class="bg-gray-100 p-2.5 rounded text-xs font-mono select-all border border-gray-200">
                            {{ $webhookVerifyToken }}
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100 text-xs text-gray-500 space-y-2">
                    <p class="font-semibold text-gray-700">Meta Webhook Steps:</p>
                    <ol class="list-decimal list-inside space-y-1">
                        <li>Go to Meta App Dashboard</li>
                        <li>Add <b>WhatsApp</b> Product</li>
                        <li>Click <b>Configuration</b> -> Edit Webhook</li>
                        <li>Paste the Callback URL & Verify Token above</li>
                        <li>Subscribe to <b>messages</b> field</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
