@php
    $siteName  = \App\Models\SiteSetting::get('site_name', 'WACRM');
    $metaTitle = \App\Models\SiteSetting::get('meta_title', $siteName . ' — Super Admin');
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
    <title>{{ $title ?? 'Super Admin' }} — {{ $siteName }}</title>
    @if($favicon)
        <link rel="icon" href="{{ asset('storage/'.$favicon) }}">
    @endif
    @if($gaId)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
        <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ $gaId }}');</script>
    @endif
    {!! $headScripts !!}
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #07090f; color: #e2e8f0; }
        .sidebar-scrollbar::-webkit-scrollbar { width: 3px; }
        .sidebar-scrollbar::-webkit-scrollbar-thumb { background: #1e2a3a; border-radius: 9999px; }
        
        /* Select and Option dropdown dark style styling */
        select {
            background-color: #0d1220 !important;
            color: #f8fafc !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        select option {
            background-color: #0d1220 !important;
            color: #f8fafc !important;
        }
    </style>
    @livewireStyles
</head>
<body class="antialiased">
<div class="flex h-screen overflow-hidden">

    {{-- Super Admin Sidebar --}}
    <aside class="w-60 flex-shrink-0 bg-gradient-to-b from-[#0d1220] to-[#060810] border-r border-white/5 flex flex-col">
        {{-- Brand --}}
        <div class="px-5 py-5 border-b border-white/5 flex items-center gap-3">
            @if($logoPath)
                <img src="{{ asset('storage/'.$logoPath) }}" alt="{{ $siteName }}" class="h-8 max-w-24 object-contain">
            @else
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-rose-500 to-orange-500 flex items-center justify-center text-white font-extrabold text-xs shadow-lg">
                    {{ strtoupper(substr($siteName, 0, 2)) }}
                </div>
                <div>
                    <div class="text-xs font-extrabold text-white tracking-wide">{{ $siteName }}</div>
                    <div class="text-[9px] font-bold text-rose-400 uppercase tracking-widest">Super Admin</div>
                </div>
            @endif
        </div>

        {{-- Nav --}}
        <nav class="flex-1 p-3 space-y-0.5 sidebar-scrollbar overflow-y-auto">
            <div class="text-[9px] font-bold text-slate-600 uppercase tracking-widest px-3 py-2">Platform</div>

            <a href="/super-admin"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->is('super-admin') ? 'bg-white/5 text-white border border-white/10' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 00-1 1m-6 0h6"/></svg>
                Overview
            </a>

            <a href="/super-admin/distributors"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->is('super-admin/distributors*') ? 'bg-white/5 text-white border border-white/10' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Distributors
            </a>

            <a href="/super-admin/businesses"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->is('super-admin/businesses*') ? 'bg-white/5 text-white border border-white/10' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Businesses
            </a>

            <a href="/super-admin/plans"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->is('super-admin/plans*') ? 'bg-white/5 text-white border border-white/10' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                Plan Pricing
            </a>

            <a href="/super-admin/branding"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->is('super-admin/branding*') ? 'bg-white/5 text-white border border-white/10' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                Branding
            </a>

            <div class="text-[9px] font-bold text-slate-600 uppercase tracking-widest px-3 py-2 mt-3">Account</div>

            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:bg-white/5 hover:text-white transition-all text-left">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </button>
            </form>
        </nav>

        {{-- Footer --}}
        <div class="p-4 border-t border-white/5">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-rose-500 to-orange-400 flex items-center justify-center text-[10px] font-extrabold text-white">
                    {{ strtoupper(substr(auth()->user()->name ?? 'SA', 0, 2)) }}
                </div>
                <div>
                    <div class="text-xs font-bold text-white">{{ auth()->user()->name ?? 'Super Admin' }}</div>
                    <div class="text-[9px] text-slate-500">{{ auth()->user()->email ?? '' }}</div>
                </div>
            </div>
        </div>
    </aside>

    {{-- Main --}}
    <main class="flex-1 overflow-y-auto bg-[#07090f]">
        {{ $slot }}
    </main>
</div>
@livewireScripts
</body>
</html>
