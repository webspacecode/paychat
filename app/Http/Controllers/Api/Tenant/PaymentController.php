<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Models\Tenant\Order;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentMethod;
use Illuminate\Http\Request;
use App\Services\Payments\PaymentService;
use App\Services\TokenService;
use App\Http\Requests\Tenant\InitiatePaymentRequest;
use App\Http\Controllers\Controller;
use App\Events\OrderCreated;


class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function list($tenantSlug)
    {
        $methods = PaymentMethod::where('enabled', true)
            ->get()
            ->map(function ($m) {
                return [
                    'type' => $m->type,          // upi / cash / gateway
                    'mode' => $m->mode,          // personal / business / gateway
                    'display_name' => strtoupper($m->type), // UI label
                    'provider' => $m->provider
                ];
            });

        return response()->json([
            'data' => $methods
        ]);
    }

    public function createPayment(String $tenantSlug, String $orderId, InitiatePaymentRequest $request, PaymentService $service)
    {
        $order = Order::find($orderId);

        return response()->json(
            $service->createPayment(
                $order,
                $request->payment_method,
                $request->amount
            )
        );
    }

    public function markSuccess(String $tenantSlug, String $paymentId, PaymentService $service)
    {
        $payment = Payment::find($paymentId);

        $order = $payment->order;

        $service->markPaymentSuccess($payment);

        $tokenService = new TokenService();

        $token = $tokenService->generate($order);
        
        // 🔥 BROADCAST REAL-TIME
        if ($token) {
            event(new OrderCreated($order, $token));
        }

        return response()->json([
            'payment' => $payment->fresh(),
            'token' => $token,
            'order' => $order
        ]);
    }
}
