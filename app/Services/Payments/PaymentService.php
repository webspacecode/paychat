<?php

namespace App\Services\Payments;

use App\Events\PaymentQrGenerated;
use App\Exceptions\PaymentException;
use Illuminate\Support\Str;
use App\Models\Tenant\Order;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\UpiProfile;
use App\Support\Observability;
use Illuminate\Support\Facades\DB;
use App\Services\Orders\OrderService;
use App\Services\Payments\Strategies\CashPaymentStrategy;
use App\Services\Payments\Strategies\UpiPaymentStrategy;
use App\Services\Payments\Strategies\PhonePePaymentStrategy;
use SimpleSoftwareIO\QrCode\Generator;

class PaymentService
{       
    public function initiatePayment(Order $order, string $method, array $data = [])
    {
        if ($order->status === 'cancelled') {
            throw new \Exception('Cancelled order cannot accept payment');
        }

        return match($method){
            'cash' => (new CashPaymentStrategy())->pay($order,$data),
            'upi' => (new UpiPaymentStrategy())->pay($order,$data),
            'phonepe' => (new PhonePePaymentStrategy())->pay($order,$data),
            default => throw new \Exception("Invalid payment method")
        };
    }

    public function createPayment(Order $order, $method, $amount, ?int $upiProfileId = null)
    {
        if (!$order) {
            throw new \Exception('Order not found');
        }

        $amount = $this->money($amount);

        return DB::transaction(function () use ($order, $method, $amount, $upiProfileId) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status === 'cancelled') {
                throw new \Exception('Cancelled order cannot accept payment');
            }

            if ($lockedOrder->status !== 'pending_payment') {
                throw new \Exception('Order not ready for payment');
            }

            // ✅ Get tenant config
            $config = PaymentMethod::where('type', $method)
                ->where('enabled', true)
                ->first();

            if (!$config) {
                throw new \Exception('Payment method not enabled');
            }

            $this->expireReplaceablePaymentAttempts($lockedOrder);
            $remaining = $this->remainingAmount($lockedOrder);

            if ($remaining <= 0) {
                throw new PaymentException(
                    'Order already fully paid',
                    'ORDER_ALREADY_PAID',
                    422,
                    [
                        'order_total' => $this->money($lockedOrder->total),
                        'remaining_amount' => $remaining,
                    ]
                );
            }

            if ($method !== 'cash' && $amount > $remaining) {
                throw new PaymentException(
                    'Amount exceeds remaining payment',
                    'PAYMENT_AMOUNT_EXCEEDS_REMAINING',
                    422,
                    [
                        'requested_amount' => $amount,
                        'remaining_amount' => $remaining,
                        'order_total' => $this->money($lockedOrder->total),
                        'committed_payment_amount' => $this->committedPaymentAmount($lockedOrder),
                    ]
                );
            }

            // 🔥 HANDLE METHODS
            return match ($method) {

                'cash' => $this->handleCash($lockedOrder, $amount, $remaining),

                'upi' => $this->handleUpi($lockedOrder, $amount, $config, $upiProfileId),

                default => throw new \Exception("Unsupported payment method")
            };
        });
    }

    private function handleCash(Order $order, $tenderedAmount, $remaining)
    {
        $amount = min($this->money($tenderedAmount), $this->money($remaining));
        $changeReturned = max(0, $this->money($tenderedAmount - $amount));

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'amount' => $amount,
            'status' => 'success',
            'meta' => $changeReturned > 0 ? [
                'tendered_amount' => $this->money($tenderedAmount),
                'change_returned' => $changeReturned,
            ] : null,
        ]);

        $this->updateOrderPaymentStatus($order, $changeReturned);

        return $payment;
    }

    private function handleUpi(Order $order, $amount, $config, ?int $upiProfileId = null)
    {
        $profile = $this->resolveUpiProfile($order, $upiProfileId);

        if ($profile) {
            return $this->handleProfileUpi($order, $amount, $profile);
        }

        if ($config->mode === 'personal') {
            return $this->handlePersonalUpi($order, $amount, $config);
        }

        if ($config->mode === 'business') {
            return $this->handleBusinessUpi($order, $amount, $config);
        }

        throw new \Exception('Invalid UPI configuration');
    }

    private function resolveUpiProfile(Order $order, ?int $upiProfileId = null): ?UpiProfile
    {
        if ($upiProfileId) {
            $profile = UpiProfile::whereKey($upiProfileId)
                ->where('is_active', true)
                ->first();

            if (! $profile) {
                throw new \Exception('Selected UPI profile is not active');
            }

            if ($profile->location_id !== null && (int) $profile->location_id !== (int) $order->location_id) {
                throw new \Exception('Selected UPI profile is not available for this order location');
            }

            return $profile;
        }

        if ($order->location_id) {
            $locationDefault = UpiProfile::query()
                ->where('is_active', true)
                ->where('is_default', true)
                ->where('location_id', $order->location_id)
                ->orderBy('sort_order')
                ->first();

            if ($locationDefault) {
                return $locationDefault;
            }
        }

        return UpiProfile::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->whereNull('location_id')
            ->orderBy('sort_order')
            ->first();
    }

    private function handleProfileUpi(Order $order, $amount, UpiProfile $profile)
    {
        $payeeName = $profile->payee_name ?: $profile->label;
        $ref = "ORD-{$order->id}";

        $upiQr = $this->buildUpiPayload($profile->upi_id, $payeeName, $amount, $ref);
        $profileSnapshot = $this->upiProfileSnapshot($profile);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'upi',
            'mode' => 'personal',
            'provider' => null,
            'provider_ref' => $ref,
            'upi_profile_id' => $profile->id,
            'amount' => $amount,
            'status' => 'pending',
            'upi_qr_url' => $upiQr,
            'meta' => [
                'upi_id' => $profile->upi_id,
                'note' => "{$profile->label}#{$order->order_no}",
                'upi_profile' => $profileSnapshot,
            ],
        ]);

        $this->broadcastPaymentQr($order, $payment, $upiQr, $profileSnapshot);

        return $payment->fresh('upiProfile');
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

        $upiQr = $this->buildUpiPayload($upiId, $name, $amount, $ref);

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

        $this->broadcastPaymentQr($order, $payment, $upiQr);

        return $payment;
    }

    private function handleBusinessUpi(Order $order, $amount, $config)
    {
        $baseUrl   = config('services.phonepe.base_url');
        $merchantId = $config->config['merchant_id'];
        $saltKey    = $config->config['salt_key'];
        $saltIndex  = $config->config['salt_index'];

        $txnId = "ORD-" . $order->id . "-" . time();

        $payload = [
            "merchantId" => $merchantId,
            "merchantTransactionId" => $txnId,
            "merchantUserId" => "USER-" . $order->id,
            "amount" => (int) ($amount * 100), // paise
            "callbackUrl" => route('phonepe.callback'),
            "paymentInstrument" => [
                "type" => "UPI_QR"
            ]
        ];

        $encodedPayload = base64_encode(json_encode($payload));

        $endpoint = "/pg/v1/pay";

        $checksum = hash('sha256', $encodedPayload . $endpoint . $saltKey)
            . "###" . $saltIndex;

        $url = $baseUrl . $endpoint;

        $response = \Http::withHeaders([
            "Content-Type" => "application/json",
            "X-VERIFY" => $checksum
        ])->post($url, [
            "request" => $encodedPayload
        ]);

        if (!$response->successful()) {
            \Log::error("PhonePe Pay API Error", [
                'order_id' => $order->id,
                'location_id' => $order->location_id,
                'provider' => 'phonepe',
                'status' => $response->status(),
            ]);
            throw new \Exception("PhonePe API failed");
        }

        $res = $response->json();

        if (!isset($res['data']['instrumentResponse']['qrCode'])) {
            throw new \Exception("QR not returned from PhonePe");
        }

        $qr = $res['data']['instrumentResponse']['qrCode'];

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'upi',
            'mode' => 'business',
            'provider' => 'phonepe',
            'provider_ref' => $txnId,
            'amount' => $amount,
            'status' => 'pending',
            'meta' => $res
        ]);

        $this->broadcastPaymentQr($order, $payment, $qr);

        return [
            'payment' => $payment,
            'qr' => $qr
        ];
    }

    private function buildUpiPayload(string $upiId, string $payeeName, $amount, string $reference): string
    {
        return "upi://pay?" . http_build_query([
            'pa' => $upiId,
            'pn' => $payeeName,
            'am' => $amount,
            'cu' => 'INR',
            'tn' => 'Pay now',
            'tr' => $reference,
        ]);
    }

    private function upiProfileSnapshot(UpiProfile $profile): array
    {
        return [
            'id' => $profile->id,
            'label' => $profile->label,
            'upi_id' => $profile->upi_id,
            'payee_name' => $profile->payee_name,
        ];
    }

    private function publicUpiProfilePayload(?array $snapshot): ?array
    {
        if (! $snapshot) {
            return null;
        }

        return [
            'id' => $snapshot['id'] ?? null,
            'label' => $snapshot['label'] ?? null,
            'payee_name' => $snapshot['payee_name'] ?? null,
        ];
    }

    private function broadcastPaymentQr(Order $order, Payment $payment, string $upiQr, ?array $upiProfile = null): void
    {
        $qr = null;

        try {
            $qr = (new Generator())->format('svg')->size(240)->generate($upiQr);
        } catch (\Exception $e) {
            Observability::logWarning('payment.qr_generation.failed', $e, [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'location_id' => $order->location_id,
                'payment_method' => 'upi',
            ]);
            $qr = null;
        }

        event(new PaymentQrGenerated([
            'type' => 'payment_qr',
            'payment_method' => 'upi',
            'order_id' => $order->id,
            'order_no' => $order->order_no,
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'qr' => $qr ? base64_encode($qr) : null,
            'qr_payload' => $upiQr,
            'upi_profile' => $this->publicUpiProfilePayload($upiProfile),
        ]));
    }

    public function updateOrderPaymentStatus(Order $order, float $changeReturned = 0)
    {
        $paidAmount = $this->successfulPaidAmount($order);
        $balanceDue = max(0, $this->money($order->total - $paidAmount));

        if ($paidAmount >= $order->total) {

            $order->update([
                'payment_status' => 'paid',
                'paid_amount' => $paidAmount,
                'balance_due' => 0,
                'change_returned' => $this->money(($order->change_returned ?? 0) + $changeReturned),
            ]);

        } else {

            $order->update([
                'payment_status' => 'partially_paid',
                'paid_amount' => $paidAmount,
                'balance_due' => $balanceDue,
                'change_returned' => $this->money(($order->change_returned ?? 0) + $changeReturned),
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
        DB::transaction(function () use ($payment) {
            $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($lockedPayment->order_id)->lockForUpdate()->firstOrFail();

            $this->assertPaymentCanBeMarkedSuccessful($lockedPayment, $order);

            if ($lockedPayment->status !== 'success') {
                $lockedPayment->update(['status' => 'success']);
            }

            $this->syncOrderAfterPaymentSuccess($order);
        });
    }

    public function markPaymentSuccess(Payment $payment)
    {
        DB::transaction(function () use ($payment) {
            $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($lockedPayment->order_id)->lockForUpdate()->firstOrFail();

            $this->assertPaymentCanBeMarkedSuccessful($lockedPayment, $order);

            if ($lockedPayment->status !== 'success') {
                $lockedPayment->update([
                    'status' => 'success'
                ]);
            }

            $this->syncOrderAfterPaymentSuccess($order, true);
        });

        return $payment->fresh();
    }

    private function remainingAmount(Order $order): float
    {
        return max(0, $this->money($order->total - $this->committedPaymentAmount($order)));
    }

    private function expireReplaceablePaymentAttempts(Order $order): void
    {
        if ($this->successfulPaidAmount($order) > 0) {
            return;
        }

        $order->payments()
            ->where('status', 'pending')
            ->where('amount', '>=', $this->money($order->total))
            ->get()
            ->each(function (Payment $payment) {
                $payment->update([
                    'status' => 'expired',
                    'meta' => array_merge($payment->meta ?? [], [
                        'expired_reason' => 'replaced_by_new_payment_attempt',
                    ]),
                ]);
            });
    }

    private function assertPaymentCanBeMarkedSuccessful(Payment $payment, Order $order): void
    {
        if ($order->status === 'cancelled') {
            throw new \Exception('Cancelled order cannot accept payment');
        }

        if (in_array($payment->status, ['expired', 'failed'], true)) {
            throw new PaymentException(
                'Payment attempt is no longer valid',
                'PAYMENT_ATTEMPT_NOT_PAYABLE',
                422,
                [
                    'payment_id' => $payment->id,
                    'payment_status' => $payment->status,
                    'order_id' => $order->id,
                ]
            );
        }
    }

    private function syncOrderAfterPaymentSuccess(Order $order, bool $completeWhenPaid = false): void
    {
        $paidAmount = $this->successfulPaidAmount($order);
        $balanceDue = max(0, $this->money($order->total - $paidAmount));

        if ($paidAmount >= $order->total) {

            $order->update([
                'payment_status' => 'paid',
                'paid_amount' => $paidAmount,
                'balance_due' => 0,
            ]);

            if ($completeWhenPaid && $order->status === 'pending_payment') {
                app(OrderService::class)->completeOrder($order);
            }

        } else {

            $order->update([
                'payment_status' => $paidAmount > 0 ? 'partially_paid' : 'unpaid',
                'paid_amount' => $paidAmount,
                'balance_due' => $balanceDue,
            ]);
        }
    }

    private function successfulPaidAmount(Order $order): float
    {
        return $this->money($order->payments()
            ->where('status', 'success')
            ->sum('amount'));
    }

    private function committedPaymentAmount(Order $order): float
    {
        return $this->money($order->payments()
            ->whereIn('status', ['pending', 'processing', 'success'])
            ->sum('amount'));
    }

    private function money($amount): float
    {
        return round((float) $amount, 2);
    }
}
