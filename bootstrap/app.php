<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\ApiKeyMiddleware;

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
        ]);

        $middleware->group('api', [
            // remove SubstituteBindings from default api group
        ]);

        // Full protected group for tenant
        $middleware->group('api-protected', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'auth:sanctum',
            // 'throttle:api',
            'tenant',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Full protected group
        $middleware->group('api-protected-untenant', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'auth:sanctum',
            // 'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Public group (NO auth)
        $middleware->group('api-public', [
            'tenant',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
