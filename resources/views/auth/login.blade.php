<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — WACRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>* { font-family: 'Plus Jakarta Sans', sans-serif; } body { background: #06080f; }</style>
</head>
<body class="antialiased min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-500 to-violet-600 flex items-center justify-center text-white font-extrabold text-lg mx-auto mb-4 shadow-lg shadow-indigo-500/25">WA</div>
            <h1 class="text-2xl font-extrabold text-white">Welcome back</h1>
            <p class="text-slate-400 text-sm mt-1">Sign in to your WACRM account</p>
        </div>

        {{-- Card --}}
        <div class="bg-[#0d1220] border border-white/8 rounded-2xl p-8 shadow-2xl">

            @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm font-semibold px-4 py-3 rounded-xl mb-5 flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
            @endif

            <form method="POST" action="/login" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full bg-white/5 border {{ $errors->has('email') ? 'border-red-500/50' : 'border-white/10' }} rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition placeholder-slate-600"
                           placeholder="you@example.com">
                    @error('email')<p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Password</label>
                    <input type="password" name="password" required
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition placeholder-slate-600"
                           placeholder="••••••••">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded bg-white/5 border-white/20 text-indigo-500">
                    <label for="remember" class="text-sm text-slate-400 font-medium">Remember me</label>
                </div>

                <button type="submit"
                        class="w-full bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-indigo-900/30 text-sm">
                    Sign In →
                </button>
            </form>
        </div>

        <p class="text-center text-slate-600 text-xs mt-6">
            <a href="/" class="hover:text-slate-400 transition">← Back to home</a>
        </p>
    </div>
</body>
</html>
