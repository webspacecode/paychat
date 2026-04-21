<?php

namespace App\Services\Payments\Strategies;

use App\Contracts\PaymentStrategy;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;

class PhonePePaymentStrategy implements PaymentStrategy
{
    public function pay(Order $order, array $data)
    {
        $response = Http::withHeaders([
            'X-Merchant-Id'=> config('services.phonepe.merchant_id'),
            'X-Secret-Key'=> config('services.phonepe.secret_key'),
        ])->post(config('services.phonepe.base_url').'initiatePayment', [
            'amount' => $order->total,
            'order_id' => $order->order_no
        ]);

        $qr_url = $response['qr_url'] ?? null;

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'phonepe',
            'amount' => $order->total,
            'upi_qr_url' => $qr_url,
            'status' => 'pending'
        ]);

        $order->update(['status'=>'pending_payment']);

        return $payment;
    }
}
