<div class="max-w-7xl mx-auto p-6 font-sans" x-data="{ tab: 'leads', showToken: false }">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Developer Settings</h2>
            <p class="text-sm text-gray-500 mt-1">API credentials, webhooks, and multi-platform lead integrations.</p>
        </div>
        <span class="text-xs text-gray-400 bg-gray-100 px-3 py-1 rounded-full">API v1.0.0</span>
    </div>

    @if ($statusMessage)
    <div class="mb-5 p-4 rounded-xl border flex items-center justify-between {{ $statusType === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800' }}">
        <span class="text-sm font-medium">{{ $statusMessage }}</span>
        <button wire:click="$set('statusMessage', '')" class="text-gray-400 hover:text-gray-600">✕</button>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        {{-- LEFT PANEL --}}
        <div class="lg:col-span-5 space-y-5">

            {{-- API Key --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-base font-bold text-gray-900 mb-4">🔑 API Credentials</h3>
                <label class="text-sm font-semibold text-gray-700 block mb-1">Private API Token</label>
                <div class="flex gap-2">
                    <input :type="showToken ? 'text' : 'password'" value="{{ $apiKey }}" readonly
                           class="flex-1 bg-gray-50 border border-gray-300 rounded-lg px-3 py-2.5 text-sm font-mono outline-none">
                    <button @click="showToken=!showToken" class="px-3 border rounded-lg text-sm hover:bg-gray-50">👁️</button>
                    <button wire:click="regenerateApiKey"
                            onclick="return confirm('Regenerate? Existing integrations will break.')"
                            class="px-3 border border-red-200 bg-red-50 text-red-700 rounded-lg text-sm">🔄</button>
                </div>
                <p class="text-xs text-gray-400 mt-2">Bearer token for Authorization header.</p>
            </div>

            {{-- Webhook --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-base font-bold text-gray-900 mb-4">🔗 Outgoing Webhooks</h3>
                <form wire:submit.prevent="saveSettings" class="space-y-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-700 block mb-1">Destination URL</label>
                        <input type="text" wire:model="webhookUrl" placeholder="https://yourdomain.com/webhooks"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        @error('webhookUrl') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700 block mb-1">Signature Secret</label>
                        <input type="text" value="{{ $webhookSecret }}" readonly
                               class="w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-2.5 text-sm font-mono text-gray-500 outline-none">
                        <p class="text-xs text-gray-400 mt-1">Verify via X-Wacrm-Signature header (HMAC-SHA256).</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg text-sm font-semibold transition">Save</button>
                        <button type="button" wire:click="sendTestWebhook" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition">
                            <span wire:loading.remove wire:target="sendTestWebhook">🚀 Test</span>
                            <span wire:loading wire:target="sendTestWebhook">Sending...</span>
                        </button>
                    </div>
                </form>
                @if ($testResult)
                <div class="mt-4 border-t pt-4">
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-bold">Result</span>
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $testResult['success'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $testResult['status'] }}</span>
                    </div>
                    @if(isset($testResult['body']))
                    <div class="bg-gray-900 rounded-lg p-3 text-[11px] font-mono text-gray-300 max-h-36 overflow-auto"><pre>{{ $testResult['body'] }}</pre></div>
                    @endif
                    @if(isset($testResult['message']))
                    <p class="text-xs text-red-600 mt-1">{{ $testResult['message'] }}</p>
                    @endif
                </div>
                @endif
            </div>

            {{-- Meta Lead Ads --}}
            <div class="bg-white rounded-xl border border-blue-200 shadow-sm p-6">
                <h3 class="text-base font-bold text-gray-900 mb-1">📘 Meta Lead Ads Webhook</h3>
                <p class="text-xs text-gray-500 mb-4">Auto-receive Facebook & Instagram lead form submissions.</p>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-semibold text-gray-700 block mb-1">Facebook Page ID</label>
                        <input type="text" wire:model="metaPageId" placeholder="e.g. 123456789"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700 block mb-1">Verify Token</label>
                        <div class="flex gap-2">
                            <input type="text" value="{{ $metaLeadAdsVerifyToken }}" readonly
                                   class="flex-1 bg-gray-50 border border-gray-300 rounded-lg px-3 py-2.5 text-sm font-mono outline-none">
                            <button wire:click="regenerateLeadAdsVerifyToken" class="px-3 border rounded-lg text-sm hover:bg-gray-50">🔄</button>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700 block mb-1">Callback URL (copy to Meta)</label>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg px-3 py-2 text-xs font-mono text-blue-800 break-all">{{ url('/api/webhooks/meta-lead-ads') }}</div>
                    </div>
                    <button wire:click="saveSettings" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition">Save Meta Lead Ads Config</button>
                </div>
            </div>

        </div>

        {{-- RIGHT PANEL --}}
        <div class="lg:col-span-7">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden h-full flex flex-col">

                {{-- Tabs --}}
                <div class="flex border-b border-gray-200 bg-gray-50 overflow-x-auto flex-shrink-0">
                    @foreach([['leads','🔌 REST API'],['meta','📘 Meta Ads'],['google','🔍 Google Ads'],['tiktok','🎵 TikTok'],['website','🌐 Website'],['zapier','⚡ Zapier'],['webhooks','📥 Webhooks']] as [$k,$l])
                    <button @click="tab='{{ $k }}'"
                            :class="tab==='{{ $k }}' ? 'border-b-2 border-indigo-600 bg-white text-indigo-700 font-semibold' : 'text-gray-500 hover:bg-gray-100'"
                            class="px-4 py-3 text-xs whitespace-nowrap outline-none transition">{{ $l }}</button>
                    @endforeach
                </div>

                <div class="flex-1 overflow-y-auto">

                {{-- REST API --}}
                <div x-show="tab==='leads'" class="p-6 space-y-5">
                    <div><span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-0.5 rounded">POST</span> <code class="ml-2 text-sm font-mono font-bold">/api/leads</code></div>
                    <p class="text-sm text-gray-600">Send leads from any platform — landing pages, ad networks, Zapier, or code.</p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border rounded-lg overflow-hidden">
                            <thead class="bg-gray-50 text-[10px] uppercase text-gray-700"><tr><th class="px-3 py-2 text-left">Parameter</th><th class="px-3 py-2 text-left">Type</th><th class="px-3 py-2 text-left">Description</th></tr></thead>
                            <tbody class="divide-y divide-gray-100 text-gray-600">
                                <tr><td class="px-3 py-2 font-mono font-bold text-gray-900">phone</td><td class="px-3 py-2 text-red-600 font-semibold">Required</td><td class="px-3 py-2">Phone with country code</td></tr>
                                <tr><td class="px-3 py-2 font-mono">name</td><td class="px-3 py-2 text-gray-400">Optional</td><td class="px-3 py-2">Full name</td></tr>
                                <tr><td class="px-3 py-2 font-mono">email</td><td class="px-3 py-2 text-gray-400">Optional</td><td class="px-3 py-2">Email address</td></tr>
                                <tr><td class="px-3 py-2 font-mono">source</td><td class="px-3 py-2 text-gray-400">Optional</td><td class="px-3 py-2">"Google Ads", "TikTok", "Website Form"…</td></tr>
                                <tr><td class="px-3 py-2 font-mono">utm_source</td><td class="px-3 py-2 text-gray-400">Optional</td><td class="px-3 py-2">UTM source</td></tr>
                                <tr><td class="px-3 py-2 font-mono">utm_medium</td><td class="px-3 py-2 text-gray-400">Optional</td><td class="px-3 py-2">UTM medium (cpc, email…)</td></tr>
                                <tr><td class="px-3 py-2 font-mono">utm_campaign</td><td class="px-3 py-2 text-gray-400">Optional</td><td class="px-3 py-2">Campaign name</td></tr>
                                <tr><td class="px-3 py-2 font-mono">expected_value</td><td class="px-3 py-2 text-gray-400">Optional</td><td class="px-3 py-2">Deal size (numeric)</td></tr>
                                <tr><td class="px-3 py-2 font-mono">notes</td><td class="px-3 py-2 text-gray-400">Optional</td><td class="px-3 py-2">Internal notes</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div x-data="{lang:'curl'}">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Code Example</span>
                            <div class="flex gap-1 bg-gray-100 rounded p-0.5 text-[10px] font-semibold">
                                <button @click="lang='curl'" :class="lang==='curl'?'bg-white shadow text-gray-800':'text-gray-500'" class="px-2 py-1 rounded">cURL</button>
                                <button @click="lang='js'" :class="lang==='js'?'bg-white shadow text-gray-800':'text-gray-500'" class="px-2 py-1 rounded">JS</button>
                                <button @click="lang='php'" :class="lang==='php'?'bg-white shadow text-gray-800':'text-gray-500'" class="px-2 py-1 rounded">PHP</button>
                            </div>
                        </div>
                        <div x-show="lang==='curl'" class="bg-gray-950 text-green-400 rounded-xl p-4 font-mono text-[11px] overflow-x-auto leading-relaxed">
<pre>curl -X POST "{{ url('/api/leads') }}" \
  -H "Authorization: Bearer {{ $apiKey ?: 'YOUR_TOKEN' }}" \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+919876543210",
    "name": "Jane Smith",
    "source": "Google Ads",
    "utm_source": "google",
    "utm_medium": "cpc",
    "utm_campaign": "summer_2026",
    "expected_value": 7500
  }'</pre></div>
                        <div x-show="lang==='js'" class="bg-gray-950 text-yellow-400 rounded-xl p-4 font-mono text-[11px] overflow-x-auto leading-relaxed">
<pre>const params = new URLSearchParams(location.search);
fetch("{{ url('/api/leads') }}", {
  method: "POST",
  headers: {
    "Authorization": "Bearer {{ $apiKey ?: 'YOUR_TOKEN' }}",
    "Content-Type": "application/json"
  },
  body: JSON.stringify({
    phone: "+919876543210",
    name: "Jane Smith",
    source: "Website Form",
    utm_source: params.get("utm_source"),
    utm_campaign: params.get("utm_campaign")
  })
}).then(r => r.json()).then(console.log);</pre></div>
                        <div x-show="lang==='php'" class="bg-gray-950 text-blue-400 rounded-xl p-4 font-mono text-[11px] overflow-x-auto leading-relaxed">
<pre>Http::withToken('{{ $apiKey ?: 'YOUR_TOKEN' }}')
  ->post('{{ url('/api/leads') }}', [
    'phone'        => '+919876543210',
    'source'       => 'Google Ads',
    'utm_source'   => 'google',
    'utm_medium'   => 'cpc',
    'utm_campaign' => 'summer_2026',
  ]);</pre></div>
                    </div>
                </div>

                {{-- Meta Lead Ads Docs --}}
                <div x-show="tab==='meta'" class="p-6 space-y-4">
                    <h3 class="font-bold text-gray-900 text-lg">📘 Meta Lead Ads Setup</h3>
                    <p class="text-sm text-gray-600">Auto-receive Facebook & Instagram lead form submissions with full campaign attribution.</p>
                    <ol class="space-y-4">
                        @foreach([
                            ['Go to Meta Business Suite → Your Page → Settings → Subscriptions.', ''],
                            ['Click Add Subscription and paste the Callback URL:', url('/api/webhooks/meta-lead-ads')],
                            ['Paste the Verify Token from the left panel.', $metaLeadAdsVerifyToken ?? ''],
                            ['Subscribe to the leadgen field and save.', ''],
                            ['Save your Facebook Page ID in the left panel.', ''],
                        ] as $i => [$text, $code])
                        <li class="flex gap-3">
                            <span class="w-6 h-6 {{ $i===4 ? 'bg-green-600' : 'bg-blue-600' }} text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">{{ $i===4 ? '✓' : $i+1 }}</span>
                            <div class="text-sm text-gray-700">{{ $text }}
                                @if($code)<div class="mt-1 bg-blue-50 border border-blue-200 rounded px-3 py-2 font-mono text-xs text-blue-800 break-all">{{ $code }}</div>@endif
                            </div>
                        </li>
                        @endforeach
                    </ol>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800">
                        💡 Ensure your WhatsApp Access Token has <strong>leads_retrieval</strong> permission to fetch form field data.
                    </div>
                </div>

                {{-- Google Ads --}}
                <div x-show="tab==='google'" class="p-6 space-y-4">
                    <h3 class="font-bold text-gray-900 text-lg">🔍 Google Ads Integration</h3>
                    <p class="text-sm text-gray-600">Route Google Lead Form ad submissions to WACRM via Zapier or GTM.</p>
                    <div class="border rounded-xl p-4 bg-gray-50 space-y-2">
                        <p class="font-semibold text-sm">Via Zapier / Make</p>
                        <ol class="text-xs text-gray-600 space-y-1.5 list-decimal pl-4">
                            <li>Trigger: <em>Google Ads → New Lead Form Entry</em></li>
                            <li>Action: <em>Webhooks → POST</em> → URL: <code class="bg-gray-200 px-1 rounded">{{ url('/api/leads') }}</code></li>
                            <li>Header: <code class="bg-gray-200 px-1 rounded">Authorization: Bearer {{ $apiKey ?: 'TOKEN' }}</code></li>
                            <li>Body: map phone, name, email + set <code>"source": "Google Ads"</code>, <code>"utm_medium": "cpc"</code></li>
                        </ol>
                    </div>
                    <div class="border rounded-xl p-4 bg-gray-50 space-y-2">
                        <p class="font-semibold text-sm">Via Google Tag Manager</p>
                        <p class="text-xs text-gray-600">Fire a Custom HTML tag on form_submit event that calls <code class="bg-gray-200 px-1 rounded">POST /api/leads</code> with UTM params extracted from <code>window.location.search</code>.</p>
                    </div>
                </div>

                {{-- TikTok --}}
                <div x-show="tab==='tiktok'" class="p-6 space-y-4">
                    <h3 class="font-bold text-gray-900 text-lg">🎵 TikTok Ads Integration</h3>
                    <p class="text-sm text-gray-600">Send TikTok Lead Generation ad submissions to WACRM.</p>
                    <div class="border rounded-xl p-4 bg-gray-50 space-y-2">
                        <p class="font-semibold text-sm">Via Zapier / Make</p>
                        <ol class="text-xs text-gray-600 space-y-1.5 list-decimal pl-4">
                            <li>Trigger: <em>TikTok Lead Generation → New Lead</em></li>
                            <li>Action: <em>Webhooks → POST</em> → <code class="bg-gray-200 px-1 rounded">{{ url('/api/leads') }}</code></li>
                            <li>Set <code>"source": "TikTok Ads"</code>, <code>"utm_source": "tiktok"</code></li>
                            <li>Map <code>phone</code>, <code>name</code>, <code>email</code> from TikTok fields</li>
                        </ol>
                    </div>
                    <div class="bg-gray-950 text-pink-400 rounded-xl p-4 font-mono text-[11px] overflow-x-auto">
<pre>{
  "phone":        "@{{Lead Phone}}",
  "name":         "@{{Lead Name}}",
  "email":        "@{{Lead Email}}",
  "source":       "TikTok Ads",
  "utm_source":   "tiktok",
  "utm_medium":   "paid_social",
  "utm_campaign": "@{{Campaign Name}}"
}</pre></div>
                </div>

                {{-- Website Form --}}
                <div x-show="tab==='website'" class="p-6 space-y-4">
                    <h3 class="font-bold text-gray-900 text-lg">🌐 Website Form Integration</h3>
                    <p class="text-sm text-gray-600">Embed this JS snippet on your landing page to send leads from any contact form.</p>
                    <div class="bg-gray-950 text-cyan-400 rounded-xl p-4 font-mono text-[11px] overflow-x-auto leading-relaxed">
<pre>&lt;script&gt;
document.getElementById('lead-form')
  .addEventListener('submit', function(e) {
    e.preventDefault();
    const p = new URLSearchParams(location.search);
    fetch('{{ url('/api/leads') }}', {
      method: 'POST',
      headers: {
        'Authorization': 'Bearer {{ $apiKey ?: 'YOUR_TOKEN' }}',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        phone:        document.getElementById('phone').value,
        name:         document.getElementById('name').value,
        email:        document.getElementById('email').value,
        source:       'Website Form',
        utm_source:   p.get('utm_source')   || '',
        utm_medium:   p.get('utm_medium')   || '',
        utm_campaign: p.get('utm_campaign') || ''
      })
    }).then(r => r.json()).then(d => {
      if(d.success) alert('Submitted!');
    });
  });
&lt;/script&gt;</pre></div>
                </div>

                {{-- Zapier --}}
                <div x-show="tab==='zapier'" class="p-6 space-y-4">
                    <h3 class="font-bold text-gray-900 text-lg">⚡ Zapier / Make Integration</h3>
                    <p class="text-sm text-gray-600">Connect 6,000+ apps to WACRM. Works with any platform that has a Zapier integration.</p>
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        @foreach(['Typeform','Jotform','Google Sheets','HubSpot','Salesforce','Instagram Leads','LinkedIn Lead Gen','Shopify'] as $app)
                        <div class="border rounded-lg p-2.5 text-xs text-gray-700 bg-gray-50 font-medium">✅ {{ $app }}</div>
                        @endforeach
                    </div>
                    <div class="border rounded-xl p-4 bg-orange-50 border-orange-200">
                        <p class="font-semibold text-sm text-orange-900 mb-3">Zapier / Make Webhook Config</p>
                        <div class="space-y-2 text-xs">
                            <div><strong>URL:</strong> <code class="bg-orange-100 px-1 rounded">{{ url('/api/leads') }}</code></div>
                            <div><strong>Method:</strong> POST</div>
                            <div><strong>Header:</strong> <code class="bg-orange-100 px-1 rounded">Authorization: Bearer {{ $apiKey ?: 'TOKEN' }}</code></div>
                            <div><strong>Body fields:</strong> phone (required), name, email, source (set to your platform name), utm_campaign</div>
                        </div>
                    </div>
                </div>

                {{-- Webhooks --}}
                <div x-show="tab==='webhooks'" class="p-6 space-y-4">
                    <h3 class="font-bold text-gray-900 text-lg">📥 Outgoing Webhook Events</h3>
                    <p class="text-sm text-gray-600">WACRM pushes these events to your configured destination URL in real-time.</p>
                    <div class="space-y-3">
                        @foreach([
                            ['lead.created','Fired when a new lead is added from any source.'],
                            ['lead.stage_changed','Fired when a lead moves to a new pipeline stage.'],
                            ['message.received','Fired when a customer sends a WhatsApp message.'],
                        ] as [$evt,$desc])
                        <div class="border rounded-xl p-3 bg-gray-50">
                            <div class="flex justify-between items-center mb-1">
                                <code class="text-xs font-mono font-bold text-indigo-700">{{ $evt }}</code>
                                <span class="text-[10px] bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded">Outgoing</span>
                            </div>
                            <p class="text-xs text-gray-500">{{ $desc }}</p>
                        </div>
                        @endforeach
                    </div>
                    <div class="bg-gray-950 text-indigo-300 rounded-xl p-4 font-mono text-[11px] overflow-x-auto">
<pre>{
  "event": "lead.created",
  "timestamp": "2026-08-14T08:00:00Z",
  "data": {
    "phone": "+919876543210",
    "contact_name": "Jane Smith",
    "source": "Google Ads",
    "utm_campaign": "summer_2026",
    "stage": "New Lead",
    "expected_value": 7500
  }
}</pre></div>
                </div>

                </div>{{-- end overflow --}}
            </div>
        </div>

    </div>
</div>
