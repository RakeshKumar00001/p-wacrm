<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RazorpayService;
use App\Models\Business;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    protected RazorpayService $razorpay;

    public function __construct(RazorpayService $razorpay)
    {
        $this->razorpay = $razorpay;
    }

    /**
     * Show the billing renew/checkout screen.
     */
    public function renew()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        $business = $user->business;
        if (!$business) {
            return redirect('/login')->with('error', 'No business found.');
        }

        $plan = $business->plan ?? 'starter';
        $planModel = \App\Models\Plan::where('slug', $plan)->first();
        $price = $planModel ? (float) $planModel->price : ($plan === 'growth' ? 12999 : 4999);

        // Generate a new Razorpay order
        $order = $this->razorpay->createOrder(
            'receipt_biz_' . $business->id . '_' . time(),
            $price
        );

        if (!$order) {
            return view('billing.renew', [
                'business' => $business,
                'plan'     => $plan,
                'price'    => $price,
                'error'    => 'Unable to initialize payment gateway at this time. Please contact support.',
            ]);
        }

        // Log the transaction in the database
        DB::table('payment_transactions')->insert([
            'business_id'       => $business->id,
            'razorpay_order_id' => $order['id'],
            'plan'              => $plan,
            'amount'            => $price,
            'status'            => 'pending',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return view('billing.renew', [
            'business'      => $business,
            'plan'          => $plan,
            'price'         => $price,
            'razorpayKey'   => $this->razorpay->getKeyId(),
            'razorpayOrder' => $order,
        ]);
    }

    /**
     * Handle the Razorpay callback/verification response.
     */
    public function paymentCallback(Request $request)
    {
        $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        $orderId   = $request->input('razorpay_order_id');
        $paymentId = $request->input('razorpay_payment_id');
        $signature = $request->input('razorpay_signature');

        // 1. Verify signature
        $isVerified = $this->razorpay->verifySignature($orderId, $paymentId, $signature);

        if (!$isVerified) {
            Log::error("Razorpay Signature Verification Failed: Order ID {$orderId}");
            return redirect()->route('billing.renew')->with('error', 'Payment verification failed. Signature mismatch.');
        }

        // 2. Fetch the logged transaction
        $transaction = DB::table('payment_transactions')
            ->where('razorpay_order_id', $orderId)
            ->first();

        if (!$transaction) {
            Log::error("Razorpay Order not found in database: {$orderId}");
            return redirect()->route('billing.renew')->with('error', 'Payment verification failed. Transaction record not found.');
        }

        $business = Business::find($transaction->business_id);
        if (!$business) {
            return redirect()->route('login')->with('error', 'Payment verified but matching business tenant was not found.');
        }

        // 3. Mark transaction paid and extend business subscription by 30 days
        DB::transaction(function () use ($transaction, $orderId, $paymentId, $business) {
            DB::table('payment_transactions')
                ->where('razorpay_order_id', $orderId)
                ->update([
                    'razorpay_payment_id' => $paymentId,
                    'status'              => 'paid',
                    'updated_at'          => now(),
                ]);

            $business->update([
                'status'     => 'active',
                'expires_at' => now()->addDays(30),
            ]);
        });

        return redirect('/dashboard')->with('success', "Payment successful! Your 30-day WACRM subscription is active.");
    }
}
