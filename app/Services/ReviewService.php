<?php

namespace App\Services;

use App\Models\Review;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewService
{
    /**
     * Store a new review.
     */
    public function createReview(User $user, int $projectId, array $data): Review
    {
        return Review::create([
            'project_id' => $projectId,
            'reviewer_id' => $user->user_id,
            'rating' => $data['rating'],
            'content' => $data['content'],
        ]);
    }

    /**
     * Get reviews for a specific project.
     * Logic: Just fetches data. 
     * Note: Hiding names happens in the Resource/API layer, not here.
     */
    public function getProjectReviews(int $projectId): Collection
    {
        return Review::where('project_id', $projectId)
            ->with(['reviewer'])
            ->latest()
            ->get();
    }

    /**
     * Get a user's personal review history.
     */
    public function getUserReviews(int $userId): LengthAwarePaginator
    {
        return Review::where('reviewer_id', $userId)
            ->with('project')
            ->latest()
            ->paginate(10);
    }
}
