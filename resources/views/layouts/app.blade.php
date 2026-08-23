@php
    $siteName  = \App\Models\SiteSetting::get('site_name', 'WACRM');
    $metaTitle = \App\Models\SiteSetting::get('meta_title', $siteName . ' — WhatsApp CRM');
    $favicon   = \App\Models\SiteSetting::get('favicon_path');
    $gaId      = \App\Models\SiteSetting::get('google_analytics_id');
    $headScripts = \App\Models\SiteSetting::get('custom_head_scripts');
    $logoPath  = \App\Models\SiteSetting::get('logo_path');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $metaTitle }}</title>
    @if($favicon)
        <link rel="icon" href="{{ asset('storage/'.$favicon) }}">
    @endif
    @if($gaId)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
        <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ $gaId }}');</script>
    @endif
    {!! $headScripts !!}
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-900">
    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-slate-900 text-white flex flex-col flex-shrink-0">
            <!-- Brand / Logo -->
            <div class="p-5 font-bold text-xl flex items-center space-x-3 border-b border-slate-800">
                @if($logoPath)
                    <img src="{{ asset('storage/'.$logoPath) }}" alt="{{ $siteName }}" class="h-8 max-w-28 object-contain">
                @else
                    <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center text-slate-900 font-extrabold">{{ strtoupper(substr($siteName, 0, 2)) }}</div>
                    <span>{{ $siteName }} <span class="text-xs bg-green-500 text-slate-900 px-2 py-0.5 rounded font-semibold">AI</span></span>
                @endif
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">

                {{-- Core --}}
                <a href="/dashboard" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition font-medium text-sm {{ request()->is('dashboard') ? 'bg-slate-800 text-white border-l-4 border-green-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 00-1 1m-6 0h6"></path></svg>
                    <span>Dashboard</span>
                </a>

                <a href="/inbox" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition font-medium text-sm {{ request()->is('inbox') ? 'bg-slate-800 text-white border-l-4 border-green-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    <span>Shared Inbox</span>
                </a>

                {{-- CRM --}}
                <div class="pt-3 pb-1 text-[10px] font-bold text-slate-500 uppercase tracking-wider px-3">CRM</div>

                <a href="/contacts" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition font-medium text-sm {{ request()->is('contacts') ? 'bg-slate-800 text-white border-l-4 border-green-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span>Contacts</span>
                </a>

                <a href="/kanban" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition font-medium text-sm {{ request()->is('kanban') ? 'bg-slate-800 text-white border-l-4 border-green-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2m0 10V7m6 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>
                    <span>Sales Pipeline</span>
                </a>

                {{-- Marketing --}}
                <div class="pt-3 pb-1 text-[10px] font-bold text-slate-500 uppercase tracking-wider px-3">Marketing</div>

                <a href="/broadcasts" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition font-medium text-sm {{ request()->is('broadcasts') ? 'bg-slate-800 text-white border-l-4 border-green-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                    <span>Broadcasts</span>
                </a>

                <a href="/templates" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition font-medium text-sm {{ request()->is('templates') ? 'bg-slate-800 text-white border-l-4 border-green-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zm12 0a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path></svg>
                    <span>Templates</span>
                </a>

                <a href="/automations" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition font-medium text-sm {{ request()->is('automations') ? 'bg-slate-800 text-white border-l-4 border-green-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span>Automations</span>
                </a>

                {{-- AI & Config --}}
                <div class="pt-3 pb-1 text-[10px] font-bold text-slate-500 uppercase tracking-wider px-3">AI & Config</div>

                <a href="/ai-agent" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition font-medium text-sm {{ request()->is('ai-agent') ? 'bg-slate-800 text-white border-l-4 border-green-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <span class="text-indigo-200">AI Agent Setup</span>
                </a>

                <a href="/whatsapp-config" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition font-medium text-sm {{ request()->is('whatsapp-config') ? 'bg-slate-800 text-white border-l-4 border-green-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    <span>WhatsApp Setup</span>
                </a>

                <a href="/capi-config" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition font-medium text-sm {{ request()->is('capi-config') ? 'bg-slate-800 text-white border-l-4 border-green-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span>Meta CAPI</span>
                </a>

                <a href="/developer-settings" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition font-medium text-sm {{ request()->is('developer-settings') ? 'bg-slate-800 text-white border-l-4 border-green-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                    <span>Developer Settings</span>
                </a>

                {{-- System --}}
                <div class="pt-3 pb-1 text-[10px] font-bold text-slate-500 uppercase tracking-wider px-3">System</div>

                <a href="/profile" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition font-medium text-sm {{ request()->is('profile') ? 'bg-slate-800 text-white border-l-4 border-green-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span>Profile & Team</span>
                </a>
            </nav>

            <!-- User Profile Footer -->
            <div class="p-4 border-t border-slate-800 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-full bg-blue-500 flex items-center justify-center font-bold text-white">SA</div>
                    <div>
                        <div class="text-sm font-semibold text-white">Sales Agent</div>
                        <div class="text-xs text-slate-400">Business Tenant #1</div>
                    </div>
                </div>
            </div>
        </aside>


        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto bg-gray-100 flex flex-col min-h-0">

            {{-- ═══════════════════════════════════════════════════════ --}}
            {{-- IMPERSONATION BANNER — visible only while impersonating --}}
            {{-- ═══════════════════════════════════════════════════════ --}}
            @if(session('impersonating_from'))
                <div class="w-full bg-amber-500 text-amber-950 px-5 py-2.5 flex items-center justify-between text-sm font-bold shadow-md z-50 sticky top-0">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span>
                            👁️ Super Admin View — You are currently logged in as
                            <span class="underline decoration-dotted">{{ auth()->user()->name }}</span>
                            ({{ auth()->user()->email }})
                        </span>
                    </div>
                    <a href="{{ route('impersonate.stop') }}"
                       class="flex items-center gap-1.5 bg-amber-950 hover:bg-amber-900 text-amber-100 text-xs font-extrabold uppercase tracking-wider px-4 py-1.5 rounded-lg transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M11 16l-4-4m0 0l4-4m-4 4h14"/>
                        </svg>
                        Return to Super Admin
                    </a>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
