<div class="p-8 max-w-5xl mx-auto" x-data="{ tab: @entangle('activeTab') }">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Platform Branding</h1>
            <p class="text-slate-500 text-sm mt-1">Customize logo, site name, meta tags, colours and analytics.</p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-rose-500 to-orange-500 flex items-center justify-center text-white text-lg shadow-lg">🎨</div>
    </div>

    {{-- Status Message --}}
    @if($statusMessage)
    <div class="mb-6 px-4 py-3 rounded-xl text-sm font-bold flex items-center gap-2 border
        {{ $statusType === 'success' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' : 'bg-blue-500/10 border-blue-500/20 text-blue-400' }}">
        {{ $statusMessage }}
        <button wire:click="$set('statusMessage','')" class="ml-auto text-slate-400 hover:text-white">&times;</button>
    </div>
    @endif

    {{-- Tab Bar --}}
    <div class="flex gap-1 bg-white/3 border border-white/8 rounded-2xl p-1 mb-8 w-fit">
        @foreach([
            ['branding',   '🎨', 'Logo & Colours'],
            ['general',    '📝', 'Site Info'],
            ['analytics',  '📊', 'Analytics'],
        ] as [$key, $icon, $label])
        <button @click="tab = '{{ $key }}'; $wire.activeTab = '{{ $key }}'"
                :class="tab === '{{ $key }}' ? 'bg-white/10 text-white shadow-sm' : 'text-slate-500 hover:text-slate-300'"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition-all">
            <span>{{ $icon }}</span> {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- ═══════════════════════════════ TAB: Branding ═══════════════════════════════ --}}
    <div x-show="tab === 'branding'" class="space-y-6">

        {{-- Logo Upload --}}
        <div class="bg-[#0d1220] border border-white/6 rounded-2xl p-6">
            <h2 class="text-sm font-extrabold text-white uppercase tracking-widest mb-5 flex items-center gap-2">
                <span class="w-5 h-5 rounded bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-[9px] font-black">1</span>
                Platform Logo
            </h2>

            <div class="flex items-start gap-8">
                {{-- Current Logo Preview --}}
                <div class="flex-shrink-0">
                    <div class="w-32 h-20 rounded-xl border border-white/10 bg-white/5 flex items-center justify-center overflow-hidden">
                        @if($currentLogo)
                            <img src="{{ asset('storage/'.$currentLogo) }}" alt="Current Logo" class="max-h-16 max-w-28 object-contain">
                        @else
                            <div class="text-center">
                                <div class="text-2xl font-extrabold text-white/20">{{ strtoupper(substr($siteName ?: 'WA', 0, 2)) }}</div>
                                <div class="text-[9px] text-slate-600 mt-1">No logo set</div>
                            </div>
                        @endif
                    </div>
                    @if($currentLogo)
                        <button wire:click="removeLogo" wire:confirm="Remove current logo?"
                                class="mt-2 w-full text-[10px] font-bold text-red-400 hover:text-red-300 bg-red-500/5 hover:bg-red-500/10 border border-red-500/10 rounded-lg py-1 transition">
                            🗑 Remove
                        </button>
                    @endif
                </div>

                {{-- Upload --}}
                <div class="flex-1">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Upload New Logo</label>
                    <div class="border-2 border-dashed border-white/10 hover:border-indigo-500/40 rounded-xl p-6 text-center cursor-pointer transition-all relative group">
                        <input type="file" wire:model="logoUpload" accept="image/*"
                               class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                        @if($logoUpload)
                            <div class="text-emerald-400 font-bold text-sm">✓ {{ $logoUpload->getClientOriginalName() }}</div>
                            <div class="text-slate-500 text-xs mt-1">Ready to save</div>
                        @else
                            <svg class="w-8 h-8 text-slate-600 mx-auto mb-2 group-hover:text-indigo-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <div class="text-slate-400 text-sm font-semibold">Drop logo here or click to browse</div>
                            <div class="text-slate-600 text-xs mt-1">PNG, JPG, SVG, WebP — max 2MB</div>
                        @endif
                        <div wire:loading wire:target="logoUpload" class="absolute inset-0 bg-[#0d1220]/80 rounded-xl flex items-center justify-center">
                            <svg class="animate-spin h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Favicon --}}
        <div class="bg-[#0d1220] border border-white/6 rounded-2xl p-6">
            <h2 class="text-sm font-extrabold text-white uppercase tracking-widest mb-5 flex items-center gap-2">
                <span class="w-5 h-5 rounded bg-violet-500/20 text-violet-400 flex items-center justify-center text-[9px] font-black">2</span>
                Favicon (Browser Tab Icon)
            </h2>
            <div class="flex items-start gap-8">
                <div class="flex-shrink-0">
                    <div class="w-16 h-16 rounded-xl border border-white/10 bg-white/5 flex items-center justify-center overflow-hidden">
                        @if($currentFavicon)
                            <img src="{{ asset('storage/'.$currentFavicon) }}" alt="Favicon" class="w-10 h-10 object-contain">
                        @else
                            <span class="text-slate-600 text-xl">🌐</span>
                        @endif
                    </div>
                    @if($currentFavicon)
                        <button wire:click="removeFavicon" class="mt-2 w-full text-[10px] font-bold text-red-400 hover:text-red-300 bg-red-500/5 border border-red-500/10 rounded-lg py-1 transition">
                            🗑 Remove
                        </button>
                    @endif
                </div>
                <div class="flex-1">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Upload Favicon</label>
                    <div class="border-2 border-dashed border-white/10 hover:border-violet-500/40 rounded-xl p-5 text-center cursor-pointer transition-all relative">
                        <input type="file" wire:model="faviconUpload" accept="image/png,image/ico,image/svg+xml"
                               class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                        @if($faviconUpload)
                            <div class="text-emerald-400 font-bold text-sm">✓ {{ $faviconUpload->getClientOriginalName() }}</div>
                        @else
                            <div class="text-slate-400 text-sm font-semibold">Click to upload favicon</div>
                            <div class="text-slate-600 text-xs mt-1">PNG, ICO, SVG — max 512KB · Recommended 32×32px</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Primary Colour --}}
        <div class="bg-[#0d1220] border border-white/6 rounded-2xl p-6">
            <h2 class="text-sm font-extrabold text-white uppercase tracking-widest mb-5 flex items-center gap-2">
                <span class="w-5 h-5 rounded bg-rose-500/20 text-rose-400 flex items-center justify-center text-[9px] font-black">3</span>
                Brand Colour
            </h2>
            <div class="flex items-center gap-5">
                <input type="color" wire:model="primaryColor" id="primary-color-picker"
                       class="w-14 h-14 rounded-xl cursor-pointer border-0 bg-transparent p-0.5">
                <div>
                    <div class="text-white font-bold text-lg">{{ $primaryColor }}</div>
                    <div class="text-slate-500 text-xs mt-0.5">Used for buttons, highlights and active states</div>
                </div>
                <div class="ml-auto flex gap-2">
                    @foreach(['#6366f1','#10b981','#f59e0b','#ef4444','#3b82f6','#8b5cf6','#ec4899','#14b8a6'] as $c)
                    <button type="button" wire:click="$set('primaryColor', '{{ $c }}')"
                            class="w-7 h-7 rounded-lg border-2 transition-all hover:scale-110 {{ $primaryColor === $c ? 'border-white scale-110' : 'border-transparent' }}"
                            style="background: {{ $c }}"></button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Save Branding --}}
        <div class="flex justify-end">
            <button wire:click="saveLogo" wire:loading.attr="disabled" wire:loading.class="opacity-60"
                    class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-bold text-sm px-8 py-3 rounded-xl transition shadow-lg flex items-center gap-2">
                <span wire:loading.remove wire:target="saveLogo">💾 Save Logo & Colours</span>
                <span wire:loading wire:target="saveLogo" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> Saving…
                </span>
            </button>
        </div>
    </div>

    {{-- ═══════════════════════════════ TAB: General ═══════════════════════════════ --}}
    <div x-show="tab === 'general'" class="space-y-6" style="display:none">
        <div class="bg-[#0d1220] border border-white/6 rounded-2xl p-6 space-y-5">
            <h2 class="text-sm font-extrabold text-white uppercase tracking-widest flex items-center gap-2">
                <span class="text-lg">📝</span> Site Identity
            </h2>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Platform / Site Name *</label>
                    <input type="text" wire:model="siteName" placeholder="e.g. WACRM"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                    @error('siteName')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Tagline</label>
                    <input type="text" wire:model="siteTagline" placeholder="e.g. WhatsApp CRM & Sales Automation"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                </div>
            </div>

            <div>
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Browser Tab / Meta Title *</label>
                <input type="text" wire:model="metaTitle" placeholder="e.g. WACRM — WhatsApp CRM"
                       class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                <p class="text-slate-600 text-xs mt-1">{{ strlen($metaTitle) }} / 160 characters (appears in browser tabs & Google)</p>
            </div>

            <div>
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Meta Description (SEO)</label>
                <textarea wire:model="metaDescription" rows="2" placeholder="Short description shown in Google search results…"
                          class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition resize-none"></textarea>
                <p class="text-slate-600 text-xs mt-1">{{ strlen($metaDescription) }} / 300 characters</p>
            </div>

            <div class="border-t border-white/5 pt-5 grid grid-cols-2 gap-5">
                <div>
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Support Email</label>
                    <input type="email" wire:model="supportEmail" placeholder="support@yourapp.com"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                    @error('supportEmail')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Support Phone</label>
                    <input type="text" wire:model="supportPhone" placeholder="+91 98XXX XXXXX"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                </div>
            </div>

            <div>
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Copyright Text (Footer)</label>
                <input type="text" wire:model="copyrightText" placeholder="© 2026 WACRM. All rights reserved."
                       class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
            </div>
        </div>

        <div class="flex justify-end">
            <button wire:click="saveGeneral" wire:loading.attr="disabled" wire:loading.class="opacity-60"
                    class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-bold text-sm px-8 py-3 rounded-xl transition shadow-lg flex items-center gap-2">
                <span wire:loading.remove wire:target="saveGeneral">💾 Save Site Info</span>
                <span wire:loading wire:target="saveGeneral" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> Saving…
                </span>
            </button>
        </div>
    </div>

    {{-- ═══════════════════════════════ TAB: Analytics ═══════════════════════════════ --}}
    <div x-show="tab === 'analytics'" class="space-y-6" style="display:none">
        <div class="bg-[#0d1220] border border-white/6 rounded-2xl p-6 space-y-5">
            <h2 class="text-sm font-extrabold text-white uppercase tracking-widest flex items-center gap-2">
                <span class="text-lg">📊</span> Analytics & Tracking
            </h2>

            <div>
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Google Analytics Measurement ID</label>
                <input type="text" wire:model="googleAnalyticsId" placeholder="e.g. G-XXXXXXXXXX"
                       class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white font-mono focus:outline-none focus:border-indigo-500 transition">
                <p class="text-slate-600 text-xs mt-1">Paste your GA4 Measurement ID. Leave blank to disable.</p>
            </div>

            <div>
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Custom &lt;head&gt; Scripts</label>
                <textarea wire:model="customHeadScripts" rows="6"
                          placeholder="<!-- Paste any custom tracking pixel, chat widget, or script tags here -->"
                          class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white font-mono focus:outline-none focus:border-indigo-500 transition resize-none leading-relaxed"></textarea>
                <p class="text-slate-600 text-xs mt-1">⚠️ These scripts are injected into every page &lt;head&gt;. Only paste trusted code.</p>
            </div>
        </div>

        <div class="flex justify-end">
            <button wire:click="saveAnalytics" wire:loading.attr="disabled" wire:loading.class="opacity-60"
                    class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-bold text-sm px-8 py-3 rounded-xl transition shadow-lg flex items-center gap-2">
                <span wire:loading.remove wire:target="saveAnalytics">💾 Save Analytics</span>
                <span wire:loading wire:target="saveAnalytics" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> Saving…
                </span>
            </button>
        </div>
    </div>

</div>
