<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WACRM — WhatsApp CRM & Sales Automation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *{font-family:'Plus Jakarta Sans',sans-serif}
        body{background:#06080f;color:#e2e8f0}
        .gradient-text{background:linear-gradient(135deg,#a5b4fc,#c084fc,#f472b6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .hero-glow{background:radial-gradient(ellipse 80% 50% at 50% -10%,rgba(99,102,241,.3),transparent 70%)}
        .card-glass{background:linear-gradient(135deg,rgba(255,255,255,.04),rgba(255,255,255,.01));border:1px solid rgba(255,255,255,.07)}
        .btn-primary{background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 0 30px rgba(99,102,241,.3);transition:all .2s}
        .btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 40px rgba(99,102,241,.5)}
        @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
        .float{animation:float 4s ease-in-out infinite}
        .pricing-popular{border-color:#6366f1!important;box-shadow:0 0 0 1px #6366f1,0 0 40px rgba(99,102,241,.2)}
    </style>
</head>
<body class="antialiased overflow-x-hidden">

<nav class="fixed top-0 left-0 right-0 z-50 border-b border-white/5 bg-[#06080f]/80 backdrop-blur-xl">
    <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-indigo-500 to-violet-600 flex items-center justify-center text-white font-extrabold text-xs">WA</div>
            <span class="font-extrabold text-sm text-white">WACRM <span class="text-[9px] bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 px-1.5 py-0.5 rounded font-bold uppercase tracking-wider ml-1">Pro</span></span>
        </div>
        <div class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-400">
            <a href="#features" class="hover:text-white transition">Features</a>
            <a href="#pricing" class="hover:text-white transition">Pricing</a>
        </div>
        <div class="flex items-center gap-3">
            <a href="/login" class="text-sm font-bold text-slate-300 hover:text-white transition px-4 py-2 rounded-xl hover:bg-white/5">Login</a>
            <a href="/register" class="btn-primary text-sm font-bold text-white px-5 py-2 rounded-xl">Get Started →</a>
        </div>
    </div>
</nav>

<section class="hero-glow min-h-screen flex items-center pt-16">
    <div class="max-w-7xl mx-auto px-6 py-24 text-center">
        <div class="inline-flex items-center gap-2 bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs font-bold px-4 py-2 rounded-full mb-8">
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
            Now with Meta CAPI + CTWA Attribution
        </div>
        <h1 class="text-5xl md:text-7xl font-black text-white leading-tight mb-6">
            Turn WhatsApp into<br><span class="gradient-text">your #1 Sales Channel</span>
        </h1>
        <p class="text-xl text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed">AI-powered CRM built for WhatsApp. Ingest leads from Meta, Google & TikTok. Qualify automatically. Close faster.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-16">
            <a href="/register" class="btn-primary text-white font-bold text-base px-8 py-4 rounded-2xl">Start Free Trial →</a>
            <a href="#features" class="text-slate-300 font-bold text-base px-8 py-4 rounded-2xl border border-white/10 hover:bg-white/5 transition">See Features ↓</a>
        </div>
        <div class="float max-w-4xl mx-auto card-glass rounded-2xl p-1">
            <div class="bg-[#0d1220] rounded-xl p-6">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-2.5 h-2.5 rounded-full bg-red-500/60"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-yellow-500/60"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-green-500/60"></div>
                    <div class="ml-4 flex-1 bg-white/5 rounded-lg h-6 flex items-center px-3 text-xs text-slate-500">app.wacrm.io/dashboard</div>
                </div>
                <div class="grid grid-cols-3 gap-3 mb-3">
                    @foreach([['Total Leads','847'],['Pipeline Value','₹48.2L'],['Revenue Won','₹12.8L']] as [$l,$v])
                    <div class="bg-white/3 rounded-xl p-3 border border-white/5 text-left">
                        <div class="text-xs text-slate-500 mb-1">{{ $l }}</div>
                        <div class="text-xl font-extrabold text-white">{{ $v }}</div>
                        <div class="text-xs text-emerald-400 font-bold mt-1">↑ 23% this month</div>
                    </div>
                    @endforeach
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-white/3 rounded-xl p-3 border border-white/5 space-y-1.5">
                        @foreach(['Meta Lead Ads → 342','Google Ads → 198','WhatsApp CTWA → 187','Website Form → 120'] as $s)
                        <div class="flex items-center gap-2 text-xs text-slate-400"><div class="w-1.5 h-1.5 rounded-full bg-indigo-400"></div>{{ $s }}</div>
                        @endforeach
                    </div>
                    <div class="bg-white/3 rounded-xl p-3 border border-white/5">
                        <div class="text-xs text-slate-500 mb-2">Pipeline Stages</div>
                        @foreach([['New Lead',40],['Qualified',65],['Proposal',30],['Won',85]] as [$st,$p])
                        <div class="mb-1.5">
                            <div class="flex justify-between text-[10px] text-slate-400 mb-0.5"><span>{{ $st }}</span><span>{{ $p }}%</span></div>
                            <div class="h-1.5 bg-white/5 rounded-full"><div class="h-1.5 bg-indigo-500 rounded-full" style="width:{{ $p }}%"></div></div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="features" class="py-24 max-w-7xl mx-auto px-6">
    <div class="text-center mb-14">
        <h2 class="text-4xl font-extrabold text-white mb-3">Everything your sales team needs</h2>
        <p class="text-slate-400 text-lg">One platform. WhatsApp + CRM + AI + Attribution.</p>
    </div>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach([
            ['💬','Shared Team Inbox','All WhatsApp chats in one place. Assign conversations, round-robin routing, and 24hr session tracking.'],
            ['🤖','AI Sales Agent','GPT-powered bot qualifies leads, answers FAQs, and hands off to human agents at the right moment.'],
            ['📊','Sales Pipeline Kanban','Drag-and-drop lead cards across custom stages. Click any card to edit full contact & deal details.'],
            ['🌐','Multi-Platform Lead Intake','Ingest leads from Meta Lead Ads, Google, TikTok, Zapier, or any REST API source automatically.'],
            ['⚡','Meta CAPI Attribution','Full CTWA click ID capture and server-side Conversions API for accurate ROAS on WhatsApp ads.'],
            ['📥','CSV Import / Export','Import thousands of leads in bulk. Export your entire pipeline — Excel-ready with UTF-8 BOM.'],
        ] as [$icon,$title,$desc])
        <div class="card-glass rounded-2xl p-6 hover:border-indigo-500/30 transition-all group">
            <div class="text-3xl mb-4">{{ $icon }}</div>
            <h3 class="text-white font-bold text-lg mb-2 group-hover:text-indigo-300 transition-colors">{{ $title }}</h3>
            <p class="text-slate-400 text-sm leading-relaxed">{{ $desc }}</p>
        </div>
        @endforeach
    </div>
</section>

<section id="pricing" class="py-24 max-w-6xl mx-auto px-6">
    <div class="text-center mb-14">
        <h2 class="text-4xl font-extrabold text-white mb-3">Simple, transparent pricing</h2>
        <p class="text-slate-400 text-lg">Scale as you grow. No hidden fees.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-6">
        @foreach($plans as $p)
        @php
            $popular = ($p->slug === 'growth');
            $priceDisplay = $p->slug === 'enterprise' ? 'Custom' : '₹' . number_format($p->price) . '/mo';
            $sub = $p->slug === 'starter' ? 'For small teams' : ($p->slug === 'growth' ? 'For growing teams' : 'For large teams');
            $featuresList = [
                $p->features['max_whatsapp'] . ' WhatsApp Number' . ($p->features['max_whatsapp'] > 1 ? 's' : ''),
                $p->features['max_agents'] . ' Agent Seat' . ($p->features['max_agents'] > 1 ? 's' : ''),
                ($p->features['ai_agent'] ? 'AI Sales Agent Automation' : 'Basic Lead Pipeline'),
                ($p->features['capi'] ? 'Meta CAPI & Ads Tracking' : 'No CAPI Attribution'),
                ($p->features['webhooks'] ? 'Developer Webhooks & API' : 'No Webhook Integrations'),
                $p->trial_days . ' Days Free Trial Period',
            ];
        @endphp
        <div class="card-glass rounded-2xl p-7 relative {{ $popular ? 'pricing-popular' : '' }}">
            @if($popular)<div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-indigo-500 to-violet-500 text-white text-[10px] font-extrabold px-4 py-1 rounded-full uppercase tracking-wider">Most Popular</div>@endif
            <div class="text-sm font-bold text-indigo-400 uppercase tracking-widest mb-2">{{ $p->name }}</div>
            <div class="text-3xl font-extrabold text-white mb-1">{{ $priceDisplay }}</div>
            <div class="text-slate-400 text-sm mb-6">{{ $sub }}</div>
            <ul class="space-y-3 mb-8">
                @foreach($featuresList as $f)
                <li class="flex items-center gap-2.5 text-sm {{ str_starts_with($f, 'No ') ? 'text-slate-500 line-through' : 'text-slate-350' }}">
                    @if(str_starts_with($f, 'No '))
                    <svg class="w-4 h-4 text-red-500/50 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    @else
                    <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    @endif
                    {{ $f }}
                </li>
                @endforeach
            </ul>
            <a href="/register?plan={{ $p->slug }}" class="{{ $popular ? 'btn-primary' : 'border border-white/10 hover:bg-white/5' }} block text-center font-bold text-white py-3 rounded-xl transition">Get Started →</a>
        </div>
        @endforeach
    </div>
</section>

<footer class="border-t border-white/5 py-10 text-center">
    <div class="flex items-center justify-center gap-3 mb-3">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-indigo-500 to-violet-600 flex items-center justify-center text-white font-extrabold text-[10px]">WA</div>
        <span class="font-extrabold text-sm text-white">WACRM</span>
    </div>
    <p class="text-slate-500 text-sm">© {{ date('Y') }} WACRM. Built for WhatsApp-first sales teams.</p>
</footer>
</body>
</html>
