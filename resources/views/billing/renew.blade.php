<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Required — WACRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>* { font-family: 'Plus Jakarta Sans', sans-serif; } body { background: #06080f; }</style>
</head>
<body class="antialiased min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-lg">
        {{-- Logo --}}
        <div class="text-center mb-6">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-500 to-violet-600 flex items-center justify-center text-white font-extrabold text-lg mx-auto mb-4 shadow-lg">WA</div>
            <h1 class="text-2xl font-extrabold text-white">Subscription Billing</h1>
            <p class="text-slate-400 text-sm mt-1">Activate or renew your WACRM workspace subscription</p>
        </div>

        {{-- Main card --}}
        <div class="bg-[#0d1220] border border-white/8 rounded-2xl p-8 shadow-2xl space-y-6">

            {{-- Message Banner --}}
            @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm font-semibold px-4 py-3 rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            @if(session('info'))
            <div class="bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-sm font-semibold px-4 py-3 rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('info') }}</span>
            </div>
            @endif

            {{-- Order Summary --}}
            <div>
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Order Summary</h3>
                <div class="bg-white/3 border border-white/5 rounded-2xl p-5 space-y-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="text-white font-bold text-base">{{ $business->name }}</h4>
                            <p class="text-slate-400 text-xs mt-0.5">Plan: {{ ucfirst($plan) }}</p>
                        </div>
                        <span class="text-indigo-400 text-xs font-bold bg-indigo-500/10 px-2.5 py-1 rounded-lg uppercase tracking-wider">30-Day Period</span>
                    </div>

                    <div class="border-t border-white/5 pt-4 flex justify-between items-baseline">
                        <span class="text-sm text-slate-400">Total Price</span>
                        <div class="text-right">
                            <span class="text-2xl font-extrabold text-white">₹{{ number_format($price) }}</span>
                            <span class="text-slate-500 text-xs">/month</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payment actions --}}
            @if(isset($error))
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm font-semibold px-4 py-3 rounded-xl text-center">
                    {{ $error }}
                </div>
            @else
                <div>
                    <button id="pay-button"
                            class="w-full bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-bold py-3.5 rounded-xl transition shadow-lg shadow-indigo-900/30 text-sm flex items-center justify-center gap-2">
                        💳 Pay with Razorpay (₹{{ number_format($price) }})
                    </button>
                    <p class="text-slate-500 text-[10px] text-center mt-3">By clicking, you will open the secure Razorpay payment gateway checkout interface.</p>
                </div>
            @endif

            {{-- Hidden Verification Form --}}
            <form id="payment-form" action="{{ route('billing.callback') }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="razorpay_order_id" value="{{ $razorpayOrder['id'] ?? '' }}">
                <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                <input type="hidden" name="razorpay_signature" id="razorpay_signature">
            </form>
        </div>

        <div class="text-center mt-6">
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="text-xs font-bold text-slate-500 hover:text-slate-400 transition">
                    ← Logout of WACRM
                </button>
            </form>
        </div>
    </div>

    {{-- Razorpay Checkout script --}}
    @if(isset($razorpayOrder))
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        var options = {
            "key": "{{ $razorpayKey }}",
            "amount": "{{ $razorpayOrder['amount'] }}",
            "currency": "INR",
            "name": "WACRM Pro",
            "description": "30-Day Subscription ({{ ucfirst($plan) }} Plan)",
            "order_id": "{{ $razorpayOrder['id'] }}",
            "handler": function (response){
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.getElementById('razorpay_signature').value = response.razorpay_signature;
                document.getElementById('payment-form').submit();
            },
            "prefill": {
                "name": "{{ $business->name }}",
                "email": "{{ $business->owner_email }}",
                "contact": "{{ $business->owner_phone }}"
            },
            "theme": {
                "color": "#6366f1"
            }
        };
        var rzp1 = new Razorpay(options);
        document.getElementById('pay-button').onclick = function(e){
            rzp1.open();
            e.preventDefault();
        }
    </script>
    @endif

</body>
</html>
