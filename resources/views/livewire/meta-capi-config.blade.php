<div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-sm font-sans mt-8">
    <h2 class="text-2xl font-bold text-gray-900 mb-6 border-b pb-4">Meta Conversions API (CAPI) Configuration</h2>

    @if (session()->has('message'))
        <div class="mb-4 p-4 text-green-700 bg-green-100 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="saveConfiguration">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Dataset ID / Pixel ID</label>
                <input type="text" wire:model="pixelId" placeholder="e.g. 123456789012345" class="w-full border-gray-300 rounded-md shadow-sm p-3 border focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Access Token</label>
                <input type="password" wire:model="capiToken" placeholder="EAAI..." class="w-full border-gray-300 rounded-md shadow-sm p-3 border focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>
        </div>

        <h3 class="text-lg font-bold text-gray-800 mb-4">Event Mapping</h3>
        <p class="text-sm text-gray-500 mb-6">Map your CRM lead stages to standard Meta events (e.g. Lead, Purchase). When a lead moves to this stage, the event will be sent to Meta automatically.</p>

        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-8 space-y-4">
            <div class="grid grid-cols-2 gap-4 border-b pb-2">
                <div class="font-semibold text-gray-700 text-sm uppercase tracking-wide">CRM Stage</div>
                <div class="font-semibold text-gray-700 text-sm uppercase tracking-wide">Meta Event Name</div>
            </div>
            
            @foreach($stages as $stage)
                <div class="grid grid-cols-2 gap-4 items-center">
                    <div class="font-medium text-gray-900">{{ $stage->name }}</div>
                    <div>
                        <select wire:model="mappings.{{ $stage->id }}" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                            <option value="">None (Do not send event)</option>
                            <option value="Lead">Lead</option>
                            <option value="QualifiedLead">Qualified Lead</option>
                            <option value="Proposal">Proposal</option>
                            <option value="InitiateCheckout">Initiate Checkout</option>
                            <option value="Purchase">Purchase</option>
                        </select>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex items-center space-x-4 mb-8 pt-4 border-t border-gray-200">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md shadow hover:bg-blue-700 font-medium">
                Save Configuration
            </button>
        </div>
    </form>

    <!-- Test Event Section -->
    <div class="mt-12 bg-blue-50 p-6 rounded-lg border border-blue-100">
        <h3 class="text-lg font-bold text-gray-800 mb-2">Test Conversions API</h3>
        <p class="text-sm text-gray-600 mb-4">You can send a test event to verify your connection. Go to your Facebook Events Manager and copy the Test Event Code.</p>
        
        <div class="flex items-center space-x-4">
            <input type="text" wire:model="testEventCode" placeholder="TESTXXXX" class="border-gray-300 rounded-md shadow-sm p-2 border w-64">
            <button wire:click="sendTestEvent" type="button" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-900 font-medium text-sm">
                Send Test Event
            </button>
        </div>

        @if($testResponse)
            <div class="mt-4 p-4 bg-gray-900 text-green-400 font-mono text-xs rounded-lg overflow-x-auto">
                <pre>{{ $testResponse }}</pre>
            </div>
        @endif

        @if($testError)
            <div class="mt-4 p-4 bg-red-100 text-red-700 font-mono text-xs rounded-lg overflow-x-auto">
                <pre>{{ $testError }}</pre>
            </div>
        @endif
    </div>
</div>
