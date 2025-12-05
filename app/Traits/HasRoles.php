<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;

trait HasRoles
{
    /**
     * Check if the user has a specific role.
     * * Usage: $user->hasRole('Executive')
     *
     * @param string $roleName
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains('role_name', $roleName);
        }

        return $this->roles()->where('role_name', $roleName)->exists();
    }

    /**
     * Check if the user has ANY of the provided roles.
     * * Usage: $user->hasAnyRole(['Manager', 'Associate'])
     *
     * @param array|string $roles
     * @return bool
     */
    public function hasAnyRole(array|string $roles): bool
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        if ($this->relationLoaded('roles')) {
            return $this->roles->whereIn('role_name', $roles)->isNotEmpty();
        }

        return $this->roles()->whereIn('role_name', $roles)->exists();
    }

    /**
     * Check if the user has a specific permission via their Global Roles.
     * * Usage: $user->hasPermission('reviews.create')
     * * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->relationLoaded('roles')) {
            foreach ($this->roles as $role) {
                if ($role->relationLoaded('permissions')) {
                    if ($role->permissions->contains('permission_name', $permission)) {
                        return true;
                    }
                }
                else {
                    if ($role->permissions()->where('permission_name', $permission)->exists()) {
                        return true;
                    }
                }
            }
            return false;
        }

        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('permission_name', $permission);
            })
            ->exists();
    }
}
