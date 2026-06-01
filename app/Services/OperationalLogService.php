<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class OperationalLogService
{
    private const ALLOWED_EVENTS = [
        'payment.create.failed',
        'payment.success.failed',
        'table_service.invoice.failed',
        'order.complete.failed',
        'kitchen.send_to_kitchen.failed',
        'offline.sync.failed',
        'invoice.generate.failed',
        'invoice.pdf.render_failed',
        'invoice.qr_generation.failed',
        'invoice.token_qr_generation.failed',
        'invoice.generated_view_qr_generation.failed',
        'invoice.safe_qr_generation.failed',
        'kds.consistency.paid_order_missing_token',
        'kds.consistency.table_service_without_active_session',
        'kds.consistency.batch_missing_table_display',
        'token.generation.warning',
        'token.dispatch.warning',
        'phonepe.callback.failed',
        'webhook.failed',
        'cart.stock_unavailable',
    ];

    public function write(string $level, string $event, array $context = [], ?Request $request = null): void
    {
        if (! config('observability.tenant_operational_logs_enabled', true)) {
            return;
        }

        if (! in_array($event, self::ALLOWED_EVENTS, true)) {
            return;
        }

        try {
            $request ??= app()->bound('request') ? request() : null;
            $tenant = $this->resolveTenant($context, $request);
            $row = $this->buildRow($level, $event, $context, $request, $tenant);
            $path = $this->pathFor($tenant, $row['tenant_id'] ?? null, $row['tenant_slug'] ?? null);

            File::ensureDirectoryExists(dirname($path), 0755, true);

            $json = json_encode($row, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                return;
            }

            file_put_contents($path, $json.PHP_EOL, FILE_APPEND | LOCK_EX);
        } catch (Throwable) {
            // Operational logging must never affect POS, checkout, payment, or KDS flow.
        }
    }

    private function buildRow(string $level, string $event, array $context, ?Request $request, ?Tenant $tenant): array
    {
        $exception = $context['exception'] ?? null;
        $now = Carbon::now(config('app.timezone'));
        $requestId = $context['request_id'] ?? $request?->attributes->get('request_id') ?? $request?->headers->get('X-Request-ID');
        $statusCode = $context['status_code'] ?? ($exception instanceof Throwable ? $this->statusCodeFor($exception) : null);

        return array_filter([
            'timestamp' => $now->toIso8601String(),
            'logged_at_unix' => $now->timestamp,
            'level' => $level,
            'severity' => $context['severity'] ?? $this->severityFor($level, $statusCode),
            'event' => $event,
            'module' => $context['module'] ?? $this->moduleFor($event),
            'support_code' => $context['support_code'] ?? $requestId,
            'request_id' => $requestId,
            'tenant_id' => $tenant?->id ?? $context['tenant_id'] ?? null,
            'tenant_slug' => $tenant?->slug ?? $context['tenant_slug'] ?? $request?->route('tenant_slug'),
            'location_id' => $context['location_id'] ?? $request?->input('location_id'),
            'user_id' => $context['user_id'] ?? $request?->user()?->id,
            'method' => $context['method'] ?? $request?->method(),
            'path' => $context['path'] ?? $request?->path(),
            'route' => $context['route'] ?? (optional($request?->route())->getName() ?: optional($request?->route())->uri()),
            'status_code' => $statusCode,
            'safe_message' => $this->redact($context['safe_message'] ?? $this->safeMessage($event, $exception)),
            'exception_class' => $exception instanceof Throwable ? $exception::class : ($context['exception_class'] ?? null),
            'exception_message' => $this->redact($exception instanceof Throwable ? $exception->getMessage() : ($context['exception_message'] ?? null)),
            'file' => $exception instanceof Throwable ? $this->relativePath($exception->getFile()) : ($context['file'] ?? null),
            'line' => $exception instanceof Throwable ? $exception->getLine() : ($context['line'] ?? null),
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function resolveTenant(array $context, ?Request $request): ?Tenant
    {
        if (app()->bound('currentTenant')) {
            return app('currentTenant');
        }

        $tenant = $request?->attributes->get('tenant');
        if ($tenant instanceof Tenant) {
            return $tenant;
        }

        return null;
    }

    private function pathFor(?Tenant $tenant, mixed $tenantId, mixed $tenantSlug): string
    {
        $bucket = $tenant?->id || $tenantId
            ? 'tenant-'.($tenant?->id ?? $tenantId)
            : ($tenantSlug ? 'unknown' : 'system');

        return storage_path('logs/tenant-errors/'.$bucket.'/'.Carbon::now(config('app.timezone'))->toDateString().'.log');
    }

    private function statusCodeFor(Throwable $exception): int
    {
        return match (true) {
            $exception instanceof ValidationException => 422,
            $exception instanceof AuthenticationException => 401,
            $exception instanceof AuthorizationException => 403,
            $exception instanceof ModelNotFoundException => 404,
            $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
            method_exists($exception, 'getStatusCode') => (int) $exception->getStatusCode(),
            default => 500,
        };
    }

    private function severityFor(string $level, ?int $statusCode): string
    {
        if ($level === 'error' || ($statusCode !== null && $statusCode >= 500)) {
            return 'high';
        }

        if ($statusCode !== null && $statusCode >= 400) {
            return 'medium';
        }

        return 'low';
    }

    private function moduleFor(string $event): string
    {
        return match (true) {
            str_starts_with($event, 'payment.'), str_starts_with($event, 'phonepe.'), str_starts_with($event, 'webhook.') => 'payment',
            str_starts_with($event, 'cart.') => 'cart',
            str_starts_with($event, 'invoice.'), str_starts_with($event, 'table_service.invoice.') => 'invoice',
            str_starts_with($event, 'kitchen.'), str_starts_with($event, 'kds.') => 'kds',
            str_starts_with($event, 'token.') => 'token',
            str_starts_with($event, 'offline.') => 'offline',
            str_starts_with($event, 'order.') => 'order',
            default => 'system',
        };
    }

    private function safeMessage(string $event, mixed $exception): string
    {
        if ($exception instanceof Throwable && $exception->getMessage() !== '') {
            return $exception->getMessage();
        }

        return str_replace('.', ' ', $event);
    }

    private function relativePath(string $path): string
    {
        $base = base_path().DIRECTORY_SEPARATOR;

        return str_starts_with($path, $base) ? substr($path, strlen($base)) : basename($path);
    }

    private function redact(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $patterns = [
            '/(authorization|bearer|password|token|api[_-]?key|secret|otp)(\s*[=:]\s*)([^,\s&]+)/i',
            '/(card[_-]?number|cvv|cvc)(\s*[=:]\s*)([^,\s&]+)/i',
        ];

        return preg_replace($patterns, '$1$2[redacted]', $value);
    }
}
