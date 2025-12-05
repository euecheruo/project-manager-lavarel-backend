<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view the list of projects.
     * Note: The Controller usually filters the list logic, but this guards the action endpoint.
     */
    public function viewAny(User $user): bool
    {
        // Everyone (Executives, Managers, Associates) can access the project list page.
        // The Controller's index method handles the filtering of which projects they actually see.
        return true;
    }

    /**
     * Determine whether the user can view the project details.
     */
    public function view(User $user, Project $project): bool
    {
        // 1. Executives see ALL
        // Checks the 'projects.view_all' permission assigned to Executives.
        if ($user->hasPermission('projects.view_all')) {
            return true;
        }

        // 2. Internal Advisors (Contextual Role)
        // Direct check against the pivot table 'project_advisors'.
        // This grants access even if the user is NOT on the owning Team.
        if ($user->advisedProjects()->where('project_advisors.project_id', $project->project_id)->exists()) {
            return true;
        }

        // 3. Team Members
        // Check intersection of User's Teams and Project's Assigned Teams.
        // If the user belongs to a Team that is assigned to this Project, they get access.
        return $user->teams()
            ->whereHas('projects', function ($q) use ($project) {
                $q->where('projects.project_id', $project->project_id);
            })
            ->exists();
    }

    /**
     * Determine whether the user can create projects.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('projects.create');
    }

    /**
     * Determine whether the user can update the project (Edit metadata, etc).
     */
    public function update(User $user, Project $project): bool
    {
        return $user->hasPermission('projects.update');
    }

    /**
     * Determine whether the user can delete/archive the project.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->hasPermission('projects.delete');
    }

    /**
     * Determine whether the user can assign teams to this project.
     * Custom ability mapped to 'projects.assign_teams'.
     */
    public function assignTeams(User $user, Project $project): bool
    {
        return $user->hasPermission('projects.assign_teams');
    }

    /**
     * Determine whether the user can assign internal advisors.
     * Custom ability mapped to 'projects.assign_advisors'.
     */
    public function assignAdvisors(User $user, Project $project): bool
    {
        return $user->hasPermission('projects.assign_advisors');
    }
}
