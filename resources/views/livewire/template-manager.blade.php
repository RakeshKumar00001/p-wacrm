<div class="p-8 max-w-7xl mx-auto font-sans" 
     x-data="{ 
        showCreateModal: false,
        name: 'order_update_notification',
        category: 'UTILITY',
        language: 'en_US',
        headerType: 'TEXT',
        headerText: 'Order #@{{1}} Update',
        bodyText: 'Hi @{{1}}, your order #@{{2}} for @{{3}} has been confirmed on @{{4}}. Estimated delivery: @{{5}}.',
        footerText: 'Thank you for choosing Acme Auto Solutions.',
        buttonType: 'CALL_TO_ACTION',
        
        sampleParams: {
            '1': 'John Doe',
            '2': 'ORD-9821',
            '3': 'Industrial CNC Machine',
            '4': 'August 14th',
            '5': 'August 18th'
        },

        qr1: 'Track Order',
        qr2: 'Talk to Support',
        qr3: 'Cancel Order',

        ctaUrlText: 'View Order Details',
        ctaUrl: 'https://acme.com/order/@{{2}}',
        ctaPhoneText: 'Call Dispatcher',
        ctaPhone: '+15550192834',
        ctaCopyText: 'Copy Offer Code',
        ctaCopyCode: 'SAVE2026',

        // Auto-detect all variables across header, body, and CTA URL
        getDetectedVariables() {
            let combined = (this.headerText || '') + ' ' + (this.bodyText || '') + ' ' + (this.ctaUrl || '');
            let matches = combined.match(/\{\{(\d+)\}\}/g) || [];
            let nums = matches.map(m => m.replace(/\D/g, '')).filter((v, i, a) => a.indexOf(v) === i);
            nums.sort((a, b) => parseInt(a) - parseInt(b));
            
            // Ensure sampleParams has keys for each
            nums.forEach(n => {
                if (!this.sampleParams[n]) {
                    this.sampleParams[n] = 'Sample ' + n;
                }
            });
            return nums;
        },

        getNextVarNumber() {
            let combined = (this.headerText || '') + ' ' + (this.bodyText || '');
            let matches = combined.match(/\{\{(\d+)\}\}/g) || [];
            if (!matches.length) return 1;
            let max = Math.max(...matches.map(m => parseInt(m.replace(/\D/g, ''))));
            return max + 1;
        },

        insertNextVarToBody() {
            let n = this.getNextVarNumber();
            this.bodyText = (this.bodyText || '') + ' {{' + n + '}} ';
        },

        insertNextVarToHeader() {
            let n = this.getNextVarNumber();
            this.headerText = (this.headerText || '') + ' {{' + n + '}} ';
        },

        insertVarToUrl() {
            this.ctaUrl = (this.ctaUrl || '') + '{{1}}';
        },

        getFormattedBody() {
            let text = this.bodyText || '';
            return text.replace(/\{\{(\d+)\}\}/g, (match, num) => {
                return this.sampleParams[num] || match;
            });
        },

        getFormattedHeader() {
            let text = this.headerText || '';
            return text.replace(/\{\{(\d+)\}\}/g, (match, num) => {
                return this.sampleParams[num] || match;
            });
        },

        getFormattedUrl() {
            let url = this.ctaUrl || '';
            return url.replace(/\{\{(\d+)\}\}/g, (match, num) => {
                return this.sampleParams[num] || match;
            });
        }
     }">

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">WhatsApp Message Templates & Meta Approvals</h1>
            <p class="text-gray-600 mt-1">Unlimited dynamic variables support (<code>@{{1}}</code>, <code>@{{2}}</code>, <code>@{{3}}</code> ... <code>@{{N}}</code>) with live auto-detection and 0ms instant preview.</p>
        </div>

        <div class="flex items-center space-x-3">
            <button wire:click="syncTemplatesFromMeta" class="bg-white border border-gray-300 text-gray-700 px-4 py-2.5 rounded-lg shadow-sm font-semibold hover:bg-gray-50 flex items-center space-x-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                <span>Sync with Meta</span>
            </button>

            <button @click="showCreateModal = true" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg shadow-sm font-semibold flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Create Meta Template</span>
            </button>
        </div>
    </div>

    @if($statusMessage)
        <div class="mb-6 p-4 rounded-lg flex items-center justify-between {{ $statusType === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : ($statusType === 'error' ? 'bg-red-100 text-red-800 border border-red-300' : 'bg-blue-100 text-blue-800 border border-blue-300') }}">
            <span>{{ $statusMessage }}</span>
            <button wire:click="$set('statusMessage', null)" class="text-sm font-bold opacity-75 hover:opacity-100">&times;</button>
        </div>
    @endif

    <!-- Templates Grid List -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($templates as $tmpl)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <!-- Header Bar -->
                    <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                        <div>
                            <span class="font-bold text-gray-900 block font-mono text-sm">{{ $tmpl['name'] }}</span>
                            <span class="text-xs text-gray-500 uppercase">{{ $tmpl['category'] ?? 'MARKETING' }} &bull; {{ $tmpl['language'] ?? 'en' }}</span>
                        </div>

                        <!-- Meta Approval Status Badge -->
                        @if(($tmpl['status'] ?? '') === 'APPROVED')
                            <span class="bg-green-100 text-green-800 text-xs font-extrabold px-2.5 py-1 rounded-full border border-green-300 flex items-center space-x-1">
                                <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                                <span>APPROVED</span>
                            </span>
                        @elseif(($tmpl['status'] ?? '') === 'PENDING')
                            <span class="bg-yellow-100 text-yellow-800 text-xs font-extrabold px-2.5 py-1 rounded-full border border-yellow-300 flex items-center space-x-1">
                                <span class="w-2 h-2 rounded-full bg-yellow-500 inline-block animate-pulse"></span>
                                <span>PENDING</span>
                            </span>
                        @else
                            <span class="bg-red-100 text-red-800 text-xs font-extrabold px-2.5 py-1 rounded-full border border-red-300 flex items-center space-x-1">
                                <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                                <span>REJECTED</span>
                            </span>
                        @endif
                    </div>

                    <!-- WhatsApp Message Card Box -->
                    <div class="p-4 bg-[#e5ddd5] min-h-[220px] flex flex-col justify-end">
                        <div class="bg-white p-3.5 rounded-lg shadow border border-gray-200 text-xs text-gray-800 space-y-2 max-w-[95%] relative">
                            @foreach($tmpl['components'] as $comp)
                                @if($comp['type'] === 'HEADER')
                                    <div class="font-bold text-gray-900 border-b pb-1 border-gray-100 text-sm">{{ $comp['text'] }}</div>
                                @elseif($comp['type'] === 'BODY')
                                    <div class="whitespace-pre-line text-gray-800 leading-relaxed">{{ $comp['text'] }}</div>
                                @elseif($comp['type'] === 'FOOTER')
                                    <div class="text-[10px] text-gray-400 font-medium pt-1 border-t border-gray-100">{{ $comp['text'] }}</div>
                                @elseif($comp['type'] === 'BUTTONS')
                                    <div class="pt-2 border-t border-gray-100 space-y-1.5 mt-2">
                                        @foreach($comp['buttons'] as $btn)
                                            <div class="w-full bg-gray-50 border border-gray-200 hover:bg-gray-100 text-emerald-600 font-semibold py-1.5 px-3 rounded text-center text-xs flex items-center justify-center space-x-1">
                                                <span>{{ $btn['text'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Full Meta Template Builder Modal with Unlimited Dynamic Variables -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl max-w-6xl w-full p-6 shadow-2xl space-y-5 my-6 max-h-[90vh] overflow-y-auto" @click.away="showCreateModal = false">
            
            <div class="flex items-center justify-between border-b pb-4 border-gray-100">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Meta Official WhatsApp Template Builder</h3>
                    <p class="text-xs text-gray-500">Supports unlimited dynamic variables (<code>@{{1}}</code>, <code>@{{2}}</code>, <code>@{{3}}</code> ... <code>@{{N}}</code>) with auto-detection & 0ms instant preview.</p>
                </div>
                <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-2xl">&times;</button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left Column: Form Options -->
                <div class="lg:col-span-7 space-y-5">
                    
                    <!-- Basic Info -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Template Name</label>
                            <input type="text" x-model="name" placeholder="e.g. order_update_notification" class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Category</label>
                            <select x-model="category" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-green-500 outline-none">
                                <option value="MARKETING">MARKETING</option>
                                <option value="UTILITY">UTILITY</option>
                                <option value="AUTHENTICATION">AUTHENTICATION</option>
                            </select>
                        </div>
                    </div>

                    <!-- Header Options -->
                    <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50 space-y-3">
                        <label class="block text-xs font-bold text-gray-800 uppercase">Header Type</label>
                        
                        <div class="grid grid-cols-6 gap-2 text-xs">
                            <button type="button" @click="headerType = 'NONE'" :class="headerType === 'NONE' ? 'bg-green-600 text-white font-bold' : 'bg-white text-gray-700 border'" class="py-1.5 rounded text-center">None</button>
                            <button type="button" @click="headerType = 'TEXT'" :class="headerType === 'TEXT' ? 'bg-green-600 text-white font-bold' : 'bg-white text-gray-700 border'" class="py-1.5 rounded text-center">Text</button>
                            <button type="button" @click="headerType = 'IMAGE'" :class="headerType === 'IMAGE' ? 'bg-green-600 text-white font-bold' : 'bg-white text-gray-700 border'" class="py-1.5 rounded text-center">Image</button>
                            <button type="button" @click="headerType = 'DOCUMENT'" :class="headerType === 'DOCUMENT' ? 'bg-green-600 text-white font-bold' : 'bg-white text-gray-700 border'" class="py-1.5 rounded text-center">Document</button>
                            <button type="button" @click="headerType = 'VIDEO'" :class="headerType === 'VIDEO' ? 'bg-green-600 text-white font-bold' : 'bg-white text-gray-700 border'" class="py-1.5 rounded text-center">Video</button>
                            <button type="button" @click="headerType = 'LOCATION'" :class="headerType === 'LOCATION' ? 'bg-green-600 text-white font-bold' : 'bg-white text-gray-700 border'" class="py-1.5 rounded text-center">Location</button>
                        </div>

                        <template x-if="headerType === 'TEXT'">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-600">Header Text</span>
                                    <button type="button" @click="insertNextVarToHeader()" class="bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-xs font-bold px-2.5 py-1 rounded border border-emerald-300 transition" x-text="'+ Insert Variable {{' + getNextVarNumber() + '}}'"></button>
                                </div>
                                <input type="text" x-model="headerText" placeholder="Header Title (e.g. Order #{{1}} Update)" class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                            </div>
                        </template>
                    </div>

                    <!-- Body Text & Unlimited Variables -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-gray-700 uppercase">Body Message</label>
                            
                            <!-- Smart Auto-Incrementing Variable Inserter -->
                            <button type="button" @click="insertNextVarToBody()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm transition flex items-center space-x-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span x-text="'Insert Variable {{' + getNextVarNumber() + '}}'"></span>
                            </button>
                        </div>

                        <textarea x-model="bodyText" rows="4" placeholder="Hi {{1}}, your order #{{2}} for {{3}} has been confirmed on {{4}}. Estimated delivery: {{5}}." class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none font-sans"></textarea>

                        <!-- Dynamic Auto-Detected Variable Sample Values Grid -->
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold text-slate-700 uppercase">Auto-Detected Variable Sample Testing Values</span>
                                <span class="text-[10px] text-emerald-700 font-semibold" x-text="getDetectedVariables().length + ' variable(s) detected'"></span>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs">
                                <template x-for="vNum in getDetectedVariables()" :key="vNum">
                                    <div class="bg-white p-2 rounded border border-gray-200">
                                        <label class="block text-[10px] text-emerald-800 font-bold mb-1" x-text="'Sample for {{' + vNum + '}}'"></label>
                                        <input type="text" x-model="sampleParams[vNum]" :placeholder="'Sample ' + vNum" class="w-full border rounded px-2 py-1 text-xs">
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Footer Disclaimer (Optional)</label>
                        <input type="text" x-model="footerText" placeholder="e.g. Reply STOP to opt out" class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                    </div>

                    <!-- Interactive Buttons Options -->
                    <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50 space-y-3">
                        <label class="block text-xs font-bold text-gray-800 uppercase">Interactive Buttons</label>
                        
                        <div class="flex items-center space-x-4 text-xs font-semibold">
                            <label class="flex items-center space-x-1.5 cursor-pointer">
                                <input type="radio" x-model="buttonType" value="NONE" class="text-green-600">
                                <span>None</span>
                            </label>
                            <label class="flex items-center space-x-1.5 cursor-pointer">
                                <input type="radio" x-model="buttonType" value="QUICK_REPLY" class="text-green-600">
                                <span>Quick Reply (Up to 3)</span>
                            </label>
                            <label class="flex items-center space-x-1.5 cursor-pointer">
                                <input type="radio" x-model="buttonType" value="CALL_TO_ACTION" class="text-green-600">
                                <span>Call To Action</span>
                            </label>
                        </div>

                        <!-- Quick Replies -->
                        <template x-if="buttonType === 'QUICK_REPLY'">
                            <div class="space-y-2 pt-2">
                                <input type="text" x-model="qr1" placeholder="Quick Reply 1 (e.g. Track Order)" class="w-full border rounded px-3 py-1.5 text-xs">
                                <input type="text" x-model="qr2" placeholder="Quick Reply 2 (e.g. Talk to Support)" class="w-full border rounded px-3 py-1.5 text-xs">
                                <input type="text" x-model="qr3" placeholder="Quick Reply 3 (e.g. Cancel Order)" class="w-full border rounded px-3 py-1.5 text-xs">
                            </div>
                        </template>

                        <!-- Call To Actions -->
                        <template x-if="buttonType === 'CALL_TO_ACTION'">
                            <div class="space-y-3 pt-2 text-xs">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-semibold text-gray-600">Dynamic Website URL Button</span>
                                        <button type="button" @click="insertVarToUrl()" class="text-xs text-emerald-800 font-bold bg-emerald-100 hover:bg-emerald-200 px-2.5 py-1 rounded border border-emerald-300 transition">+ Add Dynamic Variable to URL</button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="text" x-model="ctaUrlText" placeholder="Button Label (View Quotation)" class="border rounded px-2.5 py-1.5">
                                        <input type="text" x-model="ctaUrl" placeholder="URL: https://acme.com/quote/{{1}}" class="border rounded px-2.5 py-1.5 font-mono">
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" x-model="ctaPhoneText" placeholder="Button Label (Call Sales)" class="border rounded px-2.5 py-1.5">
                                    <input type="text" x-model="ctaPhone" placeholder="Phone: +15550192834" class="border rounded px-2.5 py-1.5 font-mono">
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" x-model="ctaCopyText" placeholder="Button Label (Copy Code)" class="border rounded px-2.5 py-1.5">
                                    <input type="text" x-model="ctaCopyCode" placeholder="Offer Code: PROMO2026" class="border rounded px-2.5 py-1.5 font-mono uppercase">
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="pt-2 flex items-center justify-end space-x-3">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                        <button type="button" @click="showCreateModal = false; $wire.submitTemplateToMeta()" class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold px-6 py-2.5 rounded-lg shadow-sm transition">Submit Template to Meta</button>
                    </div>
                </div>

                <!-- Right Column: Instant 0ms WhatsApp Live Phone Preview Mockup -->
                <div class="lg:col-span-5 flex flex-col items-center justify-center bg-slate-900 rounded-2xl p-6 border border-slate-800 shadow-2xl">
                    
                    <div class="w-full max-w-[310px] bg-[#efeae2] rounded-[32px] overflow-hidden shadow-2xl border-4 border-slate-700 flex flex-col h-[520px] relative">
                        
                        <!-- Phone Top Header -->
                        <div class="bg-[#075e54] text-white p-3 flex items-center space-x-3 shadow-md">
                            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center font-bold text-xs">WA</div>
                            <div>
                                <div class="font-bold text-xs">Acme Auto Solutions</div>
                                <div class="text-[9px] text-emerald-200">Verified Business Account</div>
                            </div>
                        </div>

                        <!-- Chat Screen with Instant Dynamic Content -->
                        <div class="flex-1 p-3 overflow-y-auto space-y-3 flex flex-col justify-end">
                            
                            <!-- Incoming WhatsApp Bubble -->
                            <div class="self-start w-full bg-white p-3 rounded-lg shadow-sm text-xs text-gray-800 space-y-2 border border-gray-200">
                                
                                <!-- Dynamic Header Preview -->
                                <template x-if="headerType === 'TEXT' && headerText">
                                    <div class="font-bold text-gray-900 border-b pb-1 border-gray-100 text-sm" x-text="getFormattedHeader()"></div>
                                </template>

                                <template x-if="headerType === 'IMAGE'">
                                    <div class="bg-gray-100 h-28 rounded-md flex flex-col items-center justify-center text-gray-400 font-medium text-[11px] border border-dashed border-gray-300">
                                        <svg class="w-6 h-6 mb-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <span>[Header Image Attachment]</span>
                                    </div>
                                </template>

                                <template x-if="headerType === 'DOCUMENT'">
                                    <div class="bg-blue-50 p-2 rounded-md flex items-center space-x-2 text-blue-700 text-[11px] border border-blue-200">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        <span class="font-bold">Quotation_Document.pdf</span>
                                    </div>
                                </template>

                                <template x-if="headerType === 'VIDEO'">
                                    <div class="bg-gray-900 text-white h-24 rounded-md flex items-center justify-center text-xs">
                                        ▶ Play Product Video
                                    </div>
                                </template>

                                <template x-if="headerType === 'LOCATION'">
                                    <div class="bg-emerald-50 p-2 rounded-md flex items-center space-x-2 text-emerald-800 text-[11px] border border-emerald-200">
                                        📍 <span>Live Store Location Pin</span>
                                    </div>
                                </template>

                                <!-- Dynamic Instant Body Preview -->
                                <div class="whitespace-pre-line text-gray-800 leading-relaxed" x-text="getFormattedBody()"></div>

                                <!-- Dynamic Footer Preview -->
                                <template x-if="footerText">
                                    <div class="text-[10px] text-gray-400 font-medium pt-1 border-t border-gray-100" x-text="footerText"></div>
                                </template>

                                <!-- Dynamic Instant Buttons Preview -->
                                <template x-if="buttonType === 'QUICK_REPLY'">
                                    <div class="pt-2 border-t border-gray-100 space-y-1.5 mt-2">
                                        <template x-if="qr1">
                                            <div class="w-full bg-gray-50 border border-gray-200 hover:bg-gray-100 text-emerald-600 font-semibold py-1.5 px-3 rounded text-center text-xs flex items-center justify-center space-x-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                                <span x-text="qr1"></span>
                                            </div>
                                        </template>
                                        <template x-if="qr2">
                                            <div class="w-full bg-gray-50 border border-gray-200 hover:bg-gray-100 text-emerald-600 font-semibold py-1.5 px-3 rounded text-center text-xs flex items-center justify-center space-x-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                                <span x-text="qr2"></span>
                                            </div>
                                        </template>
                                        <template x-if="qr3">
                                            <div class="w-full bg-gray-50 border border-gray-200 hover:bg-gray-100 text-emerald-600 font-semibold py-1.5 px-3 rounded text-center text-xs flex items-center justify-center space-x-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                                <span x-text="qr3"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                <template x-if="buttonType === 'CALL_TO_ACTION'">
                                    <div class="pt-2 border-t border-gray-100 space-y-1.5 mt-2">
                                        <template x-if="ctaUrlText">
                                            <div class="w-full bg-gray-50 border border-gray-200 hover:bg-gray-100 text-emerald-600 font-semibold py-1.5 px-3 rounded text-center text-xs flex items-center justify-center space-x-1">
                                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                <span x-text="ctaUrlText"></span>
                                            </div>
                                        </template>
                                        <template x-if="ctaPhoneText">
                                            <div class="w-full bg-gray-50 border border-gray-200 hover:bg-gray-100 text-emerald-600 font-semibold py-1.5 px-3 rounded text-center text-xs flex items-center justify-center space-x-1">
                                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                                <span x-text="ctaPhoneText"></span>
                                            </div>
                                        </template>
                                        <template x-if="ctaCopyText">
                                            <div class="w-full bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 text-emerald-700 font-bold py-1.5 px-3 rounded text-center text-xs flex items-center justify-center space-x-1">
                                                <span>📋</span>
                                                <span x-text="ctaCopyText + ' (' + ctaCopyCode + ')'"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                            </div>
                        </div>

                        <!-- Phone Bottom Status -->
                        <div class="bg-gray-100 p-2 text-[10px] text-center text-gray-500 font-medium border-t border-gray-200">
                            Instant WhatsApp Live Mobile Preview
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
