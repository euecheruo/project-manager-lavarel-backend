<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view the employee directory.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.view_any');
    }

    /**
     * Determine whether the user can view a specific profile.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->user_id === $model->user_id) {
            return true;
        }

        return $user->hasPermission('users.view_any');
    }

    /**
     * Determine whether the user can create new employees.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('users.create');
    }

    /**
     * Determine whether the user can update roles/names.
     */
    public function update(User $user, User $model): bool
    {
        return $user->hasPermission('users.update');
    }

    /**
     * Determine whether the user can delete (deactivate) an employee.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->hasPermission('users.delete');
    }
}
