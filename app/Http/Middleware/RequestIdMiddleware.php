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
        $requestId = $this->resolveRequestId($request);
        $request->attributes->set('request_id', $requestId);

        $this->withLogContext($request);

        try {
            $response = $next($request);
        } catch (Throwable $e) {
            $this->withLogContext($request);
            throw $e;
        }

        $this->withLogContext($request);
        $response->headers->set('X-Request-ID', $requestId);

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
}
