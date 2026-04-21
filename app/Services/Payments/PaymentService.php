<?php

namespace App\Services\Payments;

use Illuminate\Support\Str;
use App\Models\Tenant\Order;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentMethod;
use Illuminate\Support\Facades\DB;
use App\Services\Orders\OrderService;
use App\Services\Payments\Strategies\CashPaymentStrategy;
use App\Services\Payments\Strategies\UpiPaymentStrategy;
use App\Services\Payments\Strategies\PhonePePaymentStrategy;

class PaymentService
{       
    public function initiatePayment(Order $order, string $method, array $data = [])
    {
        return match($method){
            'cash' => (new CashPaymentStrategy())->pay($order,$data),
            'upi' => (new UpiPaymentStrategy())->pay($order,$data),
            'phonepe' => (new PhonePePaymentStrategy())->pay($order,$data),
            default => throw new \Exception("Invalid payment method")
        };
    }

    public function createPayment(Order $order, $method, $amount)
    {
        if ($order->status !== 'pending_payment') {
            throw new \Exception('Order not ready for payment');
        }

        // ✅ Get tenant config
        $config = PaymentMethod::where('type', $method)
            ->where('enabled', true)
            ->first();

        if (!$config) {
            throw new \Exception('Payment method not enabled');
        }

        // ✅ Check remaining
        $paidAmount = $order->payments()
            ->where('status','success')
            ->sum('amount');

        $remaining = $order->total - $paidAmount;

        if ($remaining <= 0) {
            throw new \Exception('Order already fully paid');
        }

        if ($amount > $remaining) {
            throw new \Exception('Amount exceeds remaining payment');
        }

        // 🔥 HANDLE METHODS
        return match ($method) {

            'cash' => $this->handleCash($order, $amount),

            'upi' => $this->handleUpi($order, $amount, $config),

            default => throw new \Exception("Unsupported payment method")
        };
    }

    private function handleCash(Order $order, $amount)
    {
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => $amount,
            'status' => 'success'
        ]);

        $this->updateOrderPaymentStatus($order);

        return $payment;
    }

    private function handleUpi(Order $order, $amount, $config)
    {
        if ($config->mode === 'personal') {
            return $this->handlePersonalUpi($order, $amount, $config);
        }

        if ($config->mode === 'business') {
            return $this->handleBusinessUpi($order, $amount, $config);
        }

        throw new \Exception('Invalid UPI configuration');
    }

    private function handlePersonalUpi(Order $order, $amount, $config)
    {
        $upiId = $config->config['upi_id'] ?? null;
        $name  = $config->config['name'] ?? 'Store';

        if (!$upiId) {
            throw new \Exception('UPI ID not configured');
        }

        // 🔥 Clean store name (remove spaces/special chars for better display)
        $cleanName = Str::of($name)->replace(' ', '');

        // 🔥 SHORT note (very important — UPI apps ignore long ones)
        // Keep under ~25 chars
        $note = "{$cleanName}#{$order->order_no}";

        // 🔥 System reference (used later for webhook / tracking)
        $ref = "ORD-{$order->id}";

        // 🔥 Build UPI URL
        $upiQr = "upi://pay?" . http_build_query([
            'pa' => $upiId,     // Payee UPI ID
            'pn' => $name,      // Store name
            'am' => $amount,    // Amount
            'cu' => 'INR',      // Currency
            'tn' => "Pay now",      // 👀 User-visible (keep short)
            'tr' => $ref        // 🧠 Backend reference
        ]);

        // 🔥 Create payment record
        $payment = Payment::create([
            'order_id'       => $order->id,
            'payment_method' => 'upi',
            'mode'           => 'personal',
            'provider'       => null,
            'provider_ref'   => $ref,
            'amount'         => $amount,
            'status'         => 'pending',
            'upi_qr_url'     => $upiQr,
            'meta' => [
                'upi_id' => $upiId,
                'note'   => $note
            ]
        ]);

        return $payment;
    }

    private function handleBusinessUpi(Order $order, $amount, $config)
    {
        $provider = $config->config['provider'] ?? 'phonepe';
        $ref = (string) \Str::uuid();

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'upi',
            'mode' => 'business',
            'provider' => $provider,
            'provider_ref' => $ref,
            'amount' => $amount,
            'status' => 'processing'
        ]);

        // simulate payment link (replace with real API later)
        $paymentLink = "upi://pay?ref={$ref}";

        return [
            'payment' => $payment,
            'link' => $paymentLink
        ];
    }

    public function updateOrderPaymentStatus(Order $order)
    {
        $paidAmount = $order->payments()
            ->where('status','success')
            ->sum('amount');

        if ($paidAmount >= $order->total) {

            $order->update([
                'payment_status' => 'paid'
            ]);

        } else {

            $order->update([
                'payment_status' => 'partial'
            ]);
        }
    }
    
    // public function generateUpiQr(Order $order)
    // {
    //     $upi = "upi://pay?" . http_build_query([
    //         'pa' => '9834969229@ybl',
    //         'pn' => 'Cafe 7',
    //         'am' => $order->total,
    //         'cu' => 'INR',
    //         'tn' => 'Order '.$order->order_no
    //     ]);

    //     return $upi;
    // }

    public function markSuccess(Payment $payment)
    {
        $payment->update(['status' => 'success']);

        $order = $payment->order;

        $paid = $order->payments()
            ->where('status', 'success')
            ->sum('amount');

        if ($paid >= $order->total) {
            $order->update(['payment_status' => 'paid']);
        } else {
            $order->update(['payment_status' => 'partially_paid']);
        }
    }

    public function markPaymentSuccess(Payment $payment)
    {
        DB::transaction(function () use ($payment) {

            if ($payment->status !== 'success') {
                $payment->update([
                    'status' => 'success'
                ]);
            }

            $order = $payment->order;

            $paidAmount = $order->payments()
                ->where('status','success')
                ->sum('amount');

            if ($paidAmount >= $order->total) {

                $order->update([
                    'payment_status' => 'paid'
                ]);

                app(OrderService::class)->completeOrder($order);

            } else {

                $order->update([
                    'payment_status' => 'partial'
                ]);
            }

        });

        return $payment->fresh();
    }
}
