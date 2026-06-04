<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function __construct(private PermissionService $permissions)
    {
    }

    public function handle(Request $request, Closure $next, string ...$required): Response
    {
        $user = $request->user();

        if ($user?->isMaster()) {
            return $next($request);
        }

        if (! $this->permissions->hasAny($user, $required)) {
            abort(403, 'Permission denied.');
        }

        return $next($request);
    }
}
