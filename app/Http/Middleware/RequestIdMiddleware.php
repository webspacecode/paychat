<?php

namespace App\Http\Middleware;

use App\Support\Observability;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RequestIdMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);
        $requestId = $this->resolveRequestId($request);
        $request->attributes->set('request_id', $requestId);

        $this->withLogContext($request);

        try {
            $response = $next($request);
        } catch (Throwable $e) {
            $this->withLogContext($request);
            $this->logRequestCompleted($request, $startedAt, 500, true);
            throw $e;
        }

        $this->withLogContext($request);
        $response->headers->set('X-Request-ID', $requestId);
        $this->logRequestCompleted($request, $startedAt, $response->getStatusCode());

        return $response;
    }

    private function resolveRequestId(Request $request): string
    {
        $requestId = (string) ($request->attributes->get('request_id') ?: $request->headers->get('X-Request-ID'));
        $requestId = preg_replace('/[^A-Za-z0-9._-]/', '', $requestId);

        return $requestId !== '' && strlen($requestId) <= 100
            ? $requestId
            : (string) Str::uuid();
    }

    private function withLogContext(Request $request): void
    {
        try {
            Log::withContext(Observability::context([], $request));
        } catch (Throwable) {
            // Logging context should never affect the request.
        }
    }

    private function logRequestCompleted(Request $request, float $startedAt, int $statusCode, bool $failed = false): void
    {
        $durationMs = Observability::durationMs($startedAt);
        $context = array_merge(
            $this->routeIdentifiers($request),
            [
                'duration_ms' => $durationMs,
                'status_code' => $statusCode,
            ]
        );

        if ($failed || $durationMs >= Observability::slowRequestThresholdMs()) {
            Observability::logWarningMessage('request.slow_or_failed', $context, $request);
            return;
        }

        Observability::logInfo('request.completed', $context, $request);
    }

    private function routeIdentifiers(Request $request): array
    {
        return array_filter([
            'order_id' => $this->routeValue($request, 'order') ?? $request->input('order_id'),
            'payment_id' => $this->routeValue($request, 'payment') ?? $request->input('payment_id'),
            'table_id' => $this->routeValue($request, 'table') ?? $request->input('table_id') ?? $request->input('primary_table_id'),
            'table_session_id' => $this->routeValue($request, 'session') ?? $request->input('table_session_id'),
            'batch_id' => $this->routeValue($request, 'batch'),
            'token_code' => $this->routeValue($request, 'token'),
            'location_id' => $request->input('location_id'),
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function routeValue(Request $request, string $key): mixed
    {
        $value = $request->route($key);

        if (is_object($value) && isset($value->id)) {
            return $value->id;
        }

        return $value;
    }
}
