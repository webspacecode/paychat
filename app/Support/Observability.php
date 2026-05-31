<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class Observability
{
    private const DEFAULT_SLOW_REQUEST_MS = 1500;

    public static function requestId(?Request $request = null): ?string
    {
        $request ??= request();

        return $request->attributes->get('request_id')
            ?? $request->headers->get('X-Request-ID');
    }

    public static function context(array $extra = [], ?Request $request = null): array
    {
        $request ??= request();
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : $request->attributes->get('tenant');

        return array_filter(array_merge([
            'request_id' => self::requestId($request),
            'tenant_slug' => $tenant->slug ?? $request->route('tenant_slug'),
            'tenant_id' => $tenant->id ?? null,
            'user_id' => optional($request->user())->id,
            'location_id' => $request->input('location_id'),
            'support_code' => self::requestId($request),
            'route' => optional($request->route())->getName() ?: optional($request->route())->uri(),
            'method' => $request->method(),
            'path' => $request->path(),
        ], $extra), fn ($value) => $value !== null && $value !== '');
    }

    public static function durationMs(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }

    public static function slowRequestThresholdMs(): int
    {
        return (int) env('OBSERVABILITY_SLOW_REQUEST_MS', self::DEFAULT_SLOW_REQUEST_MS);
    }

    public static function logInfo(string $message, array $extra = [], ?Request $request = null): void
    {
        try {
            Log::info($message, self::context($extra, $request));
        } catch (Throwable) {
            // Observability must never interrupt billing or order flow.
        }
    }

    public static function logNotice(string $message, array $extra = [], ?Request $request = null): void
    {
        try {
            Log::notice($message, self::context($extra, $request));
        } catch (Throwable) {
            // Observability must never interrupt billing or order flow.
        }
    }

    public static function logWarningMessage(string $message, array $extra = [], ?Request $request = null): void
    {
        try {
            Log::warning($message, self::context($extra, $request));
        } catch (Throwable) {
            // Observability must never interrupt billing or order flow.
        }
    }

    public static function logFailure(string $message, Throwable $exception, array $extra = [], ?Request $request = null): void
    {
        try {
            Log::error($message, self::context(array_merge($extra, [
                'exception_message' => $exception->getMessage(),
                'exception_class' => $exception::class,
                'exception' => $exception,
            ]), $request));
        } catch (Throwable) {
            // Observability must never interrupt billing or order flow.
        }
    }

    public static function logWarning(string $message, Throwable $exception, array $extra = [], ?Request $request = null): void
    {
        try {
            Log::warning($message, self::context(array_merge($extra, [
                'exception_message' => $exception->getMessage(),
                'exception_class' => $exception::class,
                'exception' => $exception,
            ]), $request));
        } catch (Throwable) {
            // Observability must never interrupt billing or order flow.
        }
    }
}
