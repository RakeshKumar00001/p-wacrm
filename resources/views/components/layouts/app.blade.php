<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp CRM & Sales Automation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body, .bg-\[\#f8fafc\], [class*="bg-\[\#f8fafc\]"] {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0b0f19 !important;
            color: #e2e8f0 !important;
        }
        /* Custom sleek scrollbar for sidebar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #070a10 !important;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #1e2738 !important;
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #2b3952 !important;
        }
        /* Light scrollbar for main workspace */
        .main-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .main-scrollbar::-webkit-scrollbar-track {
            background: #0b0f19 !important;
        }
        .main-scrollbar::-webkit-scrollbar-thumb {
            background: #232e42 !important;
            border-radius: 9999px;
        }
        .main-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #364866 !important;
        }

        /* --- GLOBAL METALLIC OVERRIDES --- */
        
        /* Card Panels and Containers */
        .bg-white, [class*="bg-white"] {
            background: linear-gradient(135deg, #162030 0%, #0d1522 100%) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            color: #f1f5f9 !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05), 0 4px 20px rgba(0, 0, 0, 0.45) !important;
        }

        /* Sub-panels, columns, and secondary backgrounds */
        .bg-slate-50, .bg-gray-50, [class*="bg-slate-50"], [class*="bg-gray-50"], .bg-slate-50\/20, .bg-slate-50\/50 {
            background: linear-gradient(180deg, #121b29 0%, #0b101c 100%) !important;
            border-color: rgba(255, 255, 255, 0.06) !important;
        }

        .bg-indigo-50\/60, .bg-blue-50\/60, .bg-emerald-50\/60, .bg-indigo-50, .bg-blue-50, .bg-emerald-50 {
            background: rgba(255, 255, 255, 0.03) !important;
            border-color: rgba(255, 255, 255, 0.07) !important;
        }

        /* Text color overrides */
        .text-slate-900, .text-gray-900, .text-slate-800, .text-gray-800, .text-indigo-950, .text-purple-955, .text-purple-950 {
            color: #f8fafc !important;
        }
        .text-slate-500, .text-gray-500, .text-slate-650, .text-slate-400 {
            color: #94a3b8 !important;
        }
        .text-slate-700, .text-gray-700, .text-slate-600 {
            color: #cbd5e1 !important;
        }

        /* Borders override */
        .border-slate-200, .border-gray-200, .border-slate-100, .border-gray-100, .border-slate-200\/60, .border-slate-200\/70, .border-slate-200\/80 {
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        /* Forms, Inputs, select dropdowns, and textareas */
        input[type="text"], input[type="email"], input[type="password"], select, textarea {
            background-color: #0c121e !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            color: #f8fafc !important;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.4) !important;
        }
        select option {
            background-color: #0c121e !important;
            color: #f8fafc !important;
        }
        input[type="text"]:focus, select:focus, textarea:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3) !important;
        }

        /* Tables styling */
        tr:hover {
            background-color: rgba(255, 255, 255, 0.02) !important;
        }
        thead {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }

        /* Buttons overrides (brushed steel appearance) */
        .bg-slate-900, [class*="bg-slate-900"], button.bg-slate-900 {
            background: linear-gradient(135deg, #2b3952 0%, #172030 100%) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1), 0 2px 4px rgba(0,0,0,0.3) !important;
            color: #ffffff !important;
        }
        .bg-slate-900:hover, [class*="bg-slate-900"]:hover, button.bg-slate-900:hover {
            background: linear-gradient(135deg, #374969 0%, #1e2a3e 100%) !important;
        }

        /* Generic badges */
        .bg-slate-100 {
            background-color: rgba(255, 255, 255, 0.06) !important;
            color: #94a3b8 !important;
        }

        /* Kanban header border */
        .border-b.border-slate-100 {
            border-bottom-color: rgba(255, 255, 255, 0.08) !important;
        }
    </style>
    @livewireStyles
</head>
<body class="bg-[#0b0f19] antialiased text-slate-100 main-scrollbar">
    <div class="flex h-screen overflow-hidden">
        
        <!-- Premium Modernized Metallic Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-[#162030] to-[#080d16] text-white flex flex-col flex-shrink-0 border-r border-[#26354a] shadow-[inset_-1px_0_0_rgba(255,255,255,0.05)]">
            <!-- Brand / Logo -->
            <div class="p-6 flex items-center justify-between border-b border-[#26354a]">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-tr from-indigo-500 to-violet-600 rounded-lg flex items-center justify-center text-white font-extrabold shadow-lg shadow-indigo-500/25 text-xs">WA</div>
                    <span class="font-extrabold text-sm tracking-wide text-slate-100">WACRM <span class="text-[9px] bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 px-1.5 py-0.5 rounded font-bold uppercase tracking-wider ml-1">Pro</span></span>
                </div>
            </div>

            <!-- Organized Categorized Navigation Links -->
            <nav class="flex-1 p-4 space-y-6 overflow-y-auto custom-scrollbar">
                
                <!-- Group 1: CORE WORKSPACE -->
                <div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 mb-3">Core Workspace</div>
                    <div class="space-y-1">
                        <a href="/dashboard" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->is('dashboard') ? 'bg-gradient-to-r from-[#212f45] to-[#162030] text-[#a5b4fc] border border-[#374963] shadow-[0_2px_8px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.05)]' : 'text-slate-400 hover:bg-[#131d2b] hover:text-white border border-transparent' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 01-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 00-1 1m-6 0h6"></path></svg>
                            <span>Dashboard</span>
                        </a>

                        <a href="/inbox" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->is('inbox') ? 'bg-gradient-to-r from-[#212f45] to-[#162030] text-[#a5b4fc] border border-[#374963] shadow-[0_2px_8px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.05)]' : 'text-slate-400 hover:bg-[#131d2b] hover:text-white border border-transparent' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            <span>Shared Inbox</span>
                        </a>

                        <a href="/contacts" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->is('contacts') ? 'bg-gradient-to-r from-[#212f45] to-[#162030] text-[#a5b4fc] border border-[#374963] shadow-[0_2px_8px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.05)]' : 'text-slate-400 hover:bg-[#131d2b] hover:text-white border border-transparent' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span>Contacts</span>
                        </a>

                        <a href="/kanban" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->is('kanban') ? 'bg-gradient-to-r from-[#212f45] to-[#162030] text-[#a5b4fc] border border-[#374963] shadow-[0_2px_8px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.05)]' : 'text-slate-400 hover:bg-[#131d2b] hover:text-white border border-transparent' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2m0 10V7m6 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>
                            <span>Sales Pipeline</span>
                        </a>
                    </div>
                </div>

                <!-- Group 2: CAMPAIGNS & MARKETING -->
                <div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 mb-3">Marketing & Automations</div>
                    <div class="space-y-1">
                        <a href="/broadcasts" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->is('broadcasts') ? 'bg-gradient-to-r from-[#212f45] to-[#162030] text-[#a5b4fc] border border-[#374963] shadow-[0_2px_8px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.05)]' : 'text-slate-400 hover:bg-[#131d2b] hover:text-white border border-transparent' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                            <span>WhatsApp Broadcasts</span>
                        </a>

                        <a href="/drips" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->is('drips') ? 'bg-gradient-to-r from-[#212f45] to-[#162030] text-[#a5b4fc] border border-[#374963] shadow-[0_2px_8px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.05)]' : 'text-slate-400 hover:bg-[#131d2b] hover:text-white border border-transparent' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                            <span>Drip Campaigns</span>
                        </a>

                        <a href="/automations" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->is('automations') ? 'bg-gradient-to-r from-[#212f45] to-[#162030] text-[#a5b4fc] border border-[#374963] shadow-[0_2px_8px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.05)]' : 'text-slate-400 hover:bg-[#131d2b] hover:text-white border border-transparent' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            <span>Workflow Automations</span>
                        </a>

                        <a href="/templates" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->is('templates') ? 'bg-gradient-to-r from-[#212f45] to-[#162030] text-[#a5b4fc] border border-[#374963] shadow-[0_2px_8px_rgba(0,0,0,0.3),inset_0_1px_0_rgba(255,255,255,0.05)]' : 'text-slate-400 hover:bg-[#131d2b] hover:text-white border border-transparent' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>Meta Templates</span>
                        </a>
                    </div>
                </div>

                <!-- Group 3: UNIFIED SETTINGS HUB -->
                <div x-data="{ openSettings: {{ (request()->is('whatsapp-config') || request()->is('capi-config') || request()->is('profile') || request()->is('ai-agent') || request()->is('developer-settings')) ? 'true' : 'false' }} }">
                    <div @click="openSettings = !openSettings" class="flex items-center justify-between text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 mb-3 cursor-pointer hover:text-white transition-colors">
                        <span>Settings & API Hub</span>
                        <svg class="w-3 h-3 transition-transform duration-200" :class="openSettings ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>

                    <div x-show="openSettings" x-collapse class="space-y-1 pl-1">
                        <a href="/whatsapp-config" class="flex items-center space-x-3 px-4 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is('whatsapp-config') ? 'bg-[#131d2b] text-[#a5b4fc] font-bold border border-[#2a384e] shadow-sm' : 'text-slate-400 hover:bg-[#131d2b] hover:text-white' }}">
                            <span>📱 WhatsApp Cloud API</span>
                        </a>

                        <a href="/capi-config" class="flex items-center space-x-3 px-4 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is('capi-config') ? 'bg-[#131d2b] text-[#a5b4fc] font-bold border border-[#2a384e] shadow-sm' : 'text-slate-400 hover:bg-[#131d2b] hover:text-white' }}">
                            <span>⚡ Meta CAPI Attribution</span>
                        </a>

                        <a href="/ai-agent" class="flex items-center space-x-3 px-4 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is('ai-agent') ? 'bg-[#131d2b] text-[#a5b4fc] font-bold border border-[#2a384e] shadow-sm' : 'text-slate-400 hover:bg-[#131d2b] hover:text-white' }}">
                            <span>🤖 AI Agent Config</span>
                        </a>

                        <a href="/developer-settings" class="flex items-center space-x-3 px-4 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is('developer-settings') ? 'bg-[#131d2b] text-[#a5b4fc] font-bold border border-[#2a384e] shadow-sm' : 'text-slate-400 hover:bg-[#131d2b] hover:text-white' }}">
                            <span>🛠️ Developer Settings</span>
                        </a>

                        <a href="/profile" class="flex items-center space-x-3 px-4 py-2 rounded-xl text-xs font-semibold transition-all duration-200 {{ request()->is('profile') ? 'bg-[#131d2b] text-[#a5b4fc] font-bold border border-[#2a384e] shadow-sm' : 'text-slate-400 hover:bg-[#131d2b] hover:text-white' }}">
                            <span>👤 Profile & Team Seats</span>
                        </a>
                    </div>
                </div>

            </nav>

            <!-- User Profile Footer Link & Logout Option -->
            <div class="p-4 border-t border-[#26354a] bg-[#070a10]/50 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-500 to-violet-600 flex items-center justify-center font-extrabold text-white shadow-sm text-xs border border-indigo-400/20">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                        </div>
                        <div>
                            <div class="text-xs font-extrabold text-slate-100">{{ auth()->user()->name ?? 'User' }}</div>
                            <div class="text-[9px] text-slate-500 font-semibold">{{ auth()->user()->business->name ?? 'WACRM Workspace' }}</div>
                        </div>
                    </div>
                    <a href="/profile" class="text-slate-400 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </a>
                </div>
                <form method="POST" action="/logout" class="block">
                    @csrf
                    <button type="submit" class="w-full text-left flex items-center space-x-2 text-[10px] font-bold text-red-400 hover:text-red-300 transition-colors uppercase tracking-widest px-1 py-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto bg-[#0b0f19] main-scrollbar flex flex-col min-h-0">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
