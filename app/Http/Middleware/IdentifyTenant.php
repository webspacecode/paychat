<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        $slug = $request->route('tenant_slug');

        $tenant = Tenant::where('slug', $slug)->first();

        if (!$tenant) {
            throw new NotFoundHttpException("Tenant not found.");
        }

        // Set Industry
        if ($tenant && !$request->has('industry')) {
            $request->merge([
                'industry' => $tenant->industry
            ]);
        }

        // Bind tenant globally
        app()->instance('currentTenant', $tenant);

        // Dynamically set tenant DB connection
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $tenant->database,
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        // Set default connection for tenant models
        DB::setDefaultConnection('tenant');

        return $next($request);
    }
}

