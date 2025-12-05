<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;
use App\Models\Project;

class ReviewPolicy
{
    /**
     * Determine whether the user can create a review for a specific project.
     * Note: We pass the Project model here, not a Review model.
     */
    public function create(User $user, Project $project): bool
    {
        if (!$user->hasPermission('reviews.create')) {
            return false;
        }

        $isAdvisor = $user->advisedProjects()
            ->where('project_advisors.project_id', $project->project_id)
            ->exists();
        if ($isAdvisor)
            return true;

        return $user->teams()
            ->whereHas('projects', function ($q) use ($project) {
                $q->where('projects.project_id', $project->project_id);
            })
            ->exists();
    }

    /**
     * Determine whether the user can update a review.
     */
    public function update(User $user, Review $review): bool
    {
        return $user->hasPermission('reviews.update_own')
            && $user->user_id === $review->reviewer_id;
    }

    /**
     * Determine whether the user can delete a review.
     */
    public function delete(User $user, Review $review): bool
    {
        if ($user->hasPermission('reviews.delete_any')) {
            return true;
        }

        return $user->hasPermission('reviews.delete_own')
            && $user->user_id === $review->reviewer_id;
    }

    /**
     * Custom Policy: Determine if the user can see the Reviewer's Name.
     * Used in ReviewResource.
     */
    public function viewName(User $user, Review $review): bool
    {
        if ($user->hasPermission('reviews.view_names')) {
            return true;
        }

        if ($user->user_id === $review->reviewer_id) {
            return true;
        }

        return false;
    }
}
