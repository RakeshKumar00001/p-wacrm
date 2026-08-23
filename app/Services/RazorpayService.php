<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    protected string $keyId;
    protected string $keySecret;

    public function __construct()
    {
        $this->keyId = env('RAZORPAY_KEY_ID', 'rzp_test_V0P1b4v9l23456');
        $this->keySecret = env('RAZORPAY_KEY_SECRET', 'default_key_secret');
    }

    public function getKeyId(): string
    {
        return $this->keyId;
    }

    /**
     * Create an order in Razorpay.
     * Amount is in Rupees, converted to Paise internally.
     */
    public function createOrder(string $receiptId, float $amountInRupees, string $currency = 'INR'): ?array
    {
        $amountInPaise = (int) ($amountInRupees * 100);

        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount'   => $amountInPaise,
                    'currency' => $currency,
                    'receipt'  => $receiptId,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Razorpay Order Creation Failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Razorpay API Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify payment signature from Razorpay checkout response.
     */
    public function verifySignature(string $orderId, string $paymentId, string $signature): bool
    {
        $generatedSignature = hash_hmac(
            'sha256',
            $orderId . '|' . $paymentId,
            $this->keySecret
        );

        return hash_equals($generatedSignature, $signature);
    }
}
