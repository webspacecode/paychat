<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CorrectPaymentMethodRequest;
use App\Models\Tenant\Order;
use App\Services\PaymentMethodCorrectionService;
use App\Support\Observability;
use Illuminate\Http\JsonResponse;
use Throwable;

class PaymentCorrectionController extends Controller
{
    public function store(
        string $tenantSlug,
        Order $order,
        CorrectPaymentMethodRequest $request,
        PaymentMethodCorrectionService $service
    ): JsonResponse {
        $startedAt = microtime(true);

        try {
            $response = $service->correct(
                $order,
                $request->validated(),
                $request->user(),
                $request->header('X-Idempotency-Key')
            );

            Observability::logInfo('payment_method.corrected', [
                'tenant_slug' => $tenantSlug,
                'tenant_id' => app()->bound('currentTenant') ? app('currentTenant')->id : null,
                'endpoint' => 'payment_method.correct',
                'order_id' => $order->id,
                'payment_id' => data_get($response, 'payment.id'),
                'correction_id' => data_get($response, 'correction.id'),
                'changed' => data_get($response, 'changed'),
                'duration_ms' => Observability::durationMs($startedAt),
            ], $request);

            return response()->json($response);
        } catch (Throwable $e) {
            Observability::logFailure('payment_method.correct.failed', $e, [
                'tenant_slug' => $tenantSlug,
                'tenant_id' => app()->bound('currentTenant') ? app('currentTenant')->id : null,
                'endpoint' => 'payment_method.correct',
                'order_id' => $order->id,
                'duration_ms' => Observability::durationMs($startedAt),
                'action' => 'payment_method.correct',
            ], $request);

            throw $e;
        }
    }
}
