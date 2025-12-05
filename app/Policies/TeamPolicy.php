<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Determine whether the user can view the list of teams.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('teams.view_any');
    }

    /**
     * Determine whether the user can view the team roster.
     */
    public function view(User $user, Team $team): bool
    {
        return $user->hasPermission('teams.view_any');
    }

    /**
     * Determine whether the user can create teams.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('teams.create');
    }

    /**
     * Determine whether the user can update the team (rename).
     */
    public function update(User $user, Team $team): bool
    {
        return $user->hasPermission('teams.update');
    }

    /**
     * Determine whether the user can manage the roster (add/remove users).
     */
    public function manageRoster(User $user, Team $team): bool
    {
        return $user->hasPermission('teams.manage_roster');
    }
}
