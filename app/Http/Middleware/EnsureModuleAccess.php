<?php

namespace App\Http\Middleware;

use App\Services\ModuleAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    public function __construct(private ModuleAccessService $modules)
    {
    }

    public function handle(Request $request, Closure $next, string $moduleKey, ?string $permission = null): Response
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        if (! $tenant || (! $user->isMaster() && (int) $user->tenant_id !== (int) $tenant->id)) {
            abort(403, 'Tenant access denied.');
        }

        $this->modules->ensureAccess($tenant, $user, $moduleKey, $permission);

        return $next($request);
    }
}
