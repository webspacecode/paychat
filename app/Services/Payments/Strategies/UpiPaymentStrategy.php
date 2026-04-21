<?php

namespace App\Services\Payments\Strategies;

use App\Contracts\PaymentStrategy;
use App\Models\Order;
use App\Models\Payment;

class UpiPaymentStrategy implements PaymentStrategy
{
    public function pay(Order $order, array $data)
    {
        $qr_url = "https://upi.mock/{$order->order_no}"; // can be dynamic

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'upi',
            'amount' => $order->total,
            'upi_qr_url' => $qr_url,
            'status' => 'pending'
        ]);

        $order->update(['status'=>'pending_payment']);

        return $payment;
    }
}
