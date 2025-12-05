<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class AssignmentService
{
    /**
     * Assign a list of Teams to a Project.
     * Replaces existing assignments (Sync).
     */
    public function syncProjectTeams(Project $project, array $teamIds): void
    {
        DB::transaction(function () use ($project, $teamIds) {
            $project->teams()->sync($teamIds);
        });
    }

    /**
     * Assign an "Internal Advisor" to a Project.
     * Includes business logic checks.
     */
    public function assignAdvisor(Project $project, int $userId): void
    {
        $user = User::findOrFail($userId);

        if (!$user->is_active) {
            throw new BusinessRuleException("Cannot assign inactive user.");
        }

        $project->advisors()->syncWithoutDetaching([$userId]);
    }

    /**
     * Remove an advisor from a project.
     */
    public function removeAdvisor(Project $project, int $userId): void
    {
        $project->advisors()->detach($userId);
    }
}
