<?php

namespace App\Services;

use App\Models\User;

class PermissionService
{
    public function forUser(?User $user): array
    {
        if (! $user) {
            return [];
        }

        if ($user->isMaster()) {
            return config('permissions.permissions', []);
        }

        $role = strtolower(trim((string) $user->role));

        return array_values(array_unique(config("permissions.roles.{$role}", [])));
    }

    public function has(?User $user, string $permission): bool
    {
        return in_array($permission, $this->forUser($user), true);
    }

    public function hasAny(?User $user, array $permissions): bool
    {
        if (!$permissions) {
            return true;
        }

        return (bool) array_intersect($permissions, $this->forUser($user));
    }
}
