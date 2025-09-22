<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
         $middleware->alias([
            'tenant' => \App\Http\Middleware\IdentifyTenant::class, // 👈 register alias
        ]);

        // Full protected group for tenant
        $middleware->group('api-protected', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'auth:sanctum',
            // 'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'tenant',
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
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'tenant',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
