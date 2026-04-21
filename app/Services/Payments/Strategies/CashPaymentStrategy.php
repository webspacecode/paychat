<?php

namespace App\Services\Payments\Strategies;

use App\Contracts\PaymentStrategy;
use App\Models\Order;
use App\Models\Payment;

class CashPaymentStrategy implements PaymentStrategy
{
    public function pay(Order $order, array $data)
    {
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => $order->total,
            'status' => 'pending'
        ]);

        $order->update(['status'=>'pending_payment']);

        return $payment;
    }
}
