<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

use App\Http\Middleware\ApiKeyMiddleware;
use App\Http\Middleware\RequestIdMiddleware;
use App\Support\Observability;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => \App\Http\Middleware\IdentifyTenant::class, // 👈 register alias
            'apikey' => ApiKeyMiddleware::class,
            'request.id' => RequestIdMiddleware::class,
            'master' => \App\Http\Middleware\EnsureMasterUser::class,
            'tenant.user' => \App\Http\Middleware\EnsureTenantUserBelongsToCurrentTenant::class,
            'permission' => \App\Http\Middleware\EnsurePermission::class,
            'feature' => \App\Http\Middleware\EnsureFeature::class,
            'industry' => \App\Http\Middleware\EnsureIndustry::class,
            'module.access' => \App\Http\Middleware\EnsureModuleAccess::class,
        ]);

        $middleware->group('api', [
            RequestIdMiddleware::class,
        ]);

        // Full protected group for tenant
        $middleware->group('api-protected', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'auth:sanctum',
            // 'throttle:api',
            'tenant',
            'tenant.user',
            RequestIdMiddleware::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Full protected group
        $middleware->group('api-protected-untenant', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'auth:sanctum',
            // 'throttle:api',
            RequestIdMiddleware::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Public group (NO auth)
        $middleware->group('api-public', [
            'tenant',
            RequestIdMiddleware::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            $requestId = $request->attributes->get('request_id')
                ?: $request->headers->get('X-Request-ID')
                ?: (string) Str::uuid();

            $request->attributes->set('request_id', $requestId);

            $modelNotFound = $e instanceof ModelNotFoundException
                ? $e
                : ($e->getPrevious() instanceof ModelNotFoundException ? $e->getPrevious() : null);

            $status = match (true) {
                $e instanceof ValidationException => 422,
                $e instanceof AuthenticationException => 401,
                $e instanceof AuthorizationException => 403,
                $modelNotFound instanceof ModelNotFoundException => 404,
                $e instanceof HttpExceptionInterface => $e->getStatusCode(),
                default => 500,
            };

            if ($modelNotFound instanceof ModelNotFoundException) {
                $model = class_basename($modelNotFound->getModel());
                $message = $model === 'Order'
                    ? 'Order not found. Please create a new order.'
                    : Str::of(Str::of($model)->headline()->lower()->toString())->ucfirst()." not found.";
            } else {
                $message = $status >= 500
                    ? 'Server error'
                    : ($e->getMessage() ?: 'Request failed');
            }

            $payload = [
                'message' => $message,
                'support_code' => $requestId,
            ];

            if ($modelNotFound instanceof ModelNotFoundException) {
                $payload['code'] = Str::of(class_basename($modelNotFound->getModel()))
                    ->snake()
                    ->upper()
                    ->append('_NOT_FOUND')
                    ->toString();
            }

            if ($e instanceof ValidationException) {
                $payload['errors'] = $e->errors();
            }

            Observability::logRenderedApiException($e, $status, $message, $request);

            return response()
                ->json($payload, $status)
                ->header('X-Request-ID', $requestId);
        });
    })->create();
