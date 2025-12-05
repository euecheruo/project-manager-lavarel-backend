<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Review;
use App\Services\ReviewService;
use App\Http\Requests\Reviews\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    public function __construct(protected ReviewService $reviewService)
    {
    }

    /**
     * Get Reviews for a Project.
     */
    public function index(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $reviews = $this->reviewService->getProjectReviews($project->project_id);

        return ReviewResource::collection($reviews);
    }

    /**
     * Create a Review.
     */
    public function store(StoreReviewRequest $request, Project $project)
    {
        Gate::authorize('create', [Review::class, $project]);

        $review = $this->reviewService->createReview(
            $request->user(),
            $project->project_id,
            $request->validated()
        );

        return new ReviewResource($review);
    }

    /**
     * "My Reviews" - Personal Audit Log.
     */
    public function myReviews(Request $request)
    {
        $reviews = $this->reviewService->getUserReviews($request->user()->user_id);

        return ReviewResource::collection($reviews);
    }

    /**
     * Update a Review (Author Only).
     */
    public function update(Request $request, Review $review)
    {
        Gate::authorize('update', $review);

        $request->validate([
            'rating' => 'integer|min:1|max:5',
            'content' => 'string|min:10'
        ]);

        $review->update($request->only(['rating', 'content']));

        return new ReviewResource($review);
    }

    /**
     * Delete a Review (Author or Executive).
     */
    public function destroy(Review $review)
    {
        Gate::authorize('delete', $review);

        $review->delete();

        return response()->json(['message' => 'Review deleted']);
    }
}
