<?php

namespace App\Http\Middleware;

use App\Services\TenantFeatureService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeature
{
    public function __construct(private TenantFeatureService $features)
    {
    }

    public function handle(Request $request, Closure $next, string ...$required): Response
    {
        $user = $request->user();
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        if ($user?->isMaster()) {
            return $next($request);
        }

        if (! $tenant) {
            abort(403, 'Feature unavailable.');
        }

        foreach ($required as $feature) {
            if (! $this->features->has($tenant, $feature)) {
                abort(403, 'Feature unavailable.');
            }
        }

        return $next($request);
    }
}
