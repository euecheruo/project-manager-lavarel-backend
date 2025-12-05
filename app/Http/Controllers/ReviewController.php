<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Review;
use App\Services\ReviewService;
use App\Http\Requests\Reviews\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(
 * name="Reviews",
 * description="Feedback management. Note: Reviewer names are hidden from non-executives unless the user is viewing their own review."
 * )
 */
class ReviewController extends Controller
{
    public function __construct(protected ReviewService $reviewService)
    {
    }

    /**
     * @OA\Get(
     * path="/api/projects/{project}/reviews",
     * operationId="getProjectReviews",
     * tags={"Reviews"},
     * summary="List reviews for a project",
     * description="Retrieve all reviews. Reviewer identity is masked for Managers/Associates.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="project",
     * in="path",
     * description="Project ID",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="List of reviews",
     * @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ReviewResource"))
     * ),
     * @OA\Response(response=403, description="Forbidden. User not assigned to project.")
     * )
     */
    public function index(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $reviews = $this->reviewService->getProjectReviews($project->project_id);

        return ReviewResource::collection($reviews);
    }

    /**
     * @OA\Post(
     * path="/api/projects/{project}/reviews",
     * operationId="createReview",
     * tags={"Reviews"},
     * summary="Submit a review",
     * description="Team members or Advisors can submit feedback.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="project",
     * in="path",
     * description="Project ID",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"rating", "content"},
     * @OA\Property(property="rating", type="integer", example=5, minimum=1, maximum=5, description="1 to 5 stars"),
     * @OA\Property(property="content", type="string", example="Great execution on the frontend deliverables.", minLength=10)
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Review created",
     * @OA\JsonContent(ref="#/components/schemas/ReviewResource")
     * ),
     * @OA\Response(response=422, description="Validation Error (e.g. Rating out of range)")
     * )
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
     * @OA\Get(
     * path="/api/my-reviews",
     * operationId="getMyReviews",
     * tags={"Reviews"},
     * summary="Get my review history",
     * description="Personal audit log of all reviews written by the authenticated user.",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="List of personal reviews",
     * @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ReviewResource"))
     * )
     * )
     */
    public function myReviews(Request $request)
    {
        $reviews = $this->reviewService->getUserReviews($request->user()->user_id);

        return ReviewResource::collection($reviews);
    }

    /**
     * @OA\Put(
     * path="/api/reviews/{review}",
     * operationId="updateReview",
     * tags={"Reviews"},
     * summary="Update a review",
     * description="Authors can edit their own reviews.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="review",
     * in="path",
     * description="Review ID",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="rating", type="integer", minimum=1, maximum=5),
     * @OA\Property(property="content", type="string", minLength=10)
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Review updated",
     * @OA\JsonContent(ref="#/components/schemas/ReviewResource")
     * ),
     * @OA\Response(response=403, description="Forbidden. Can only edit own reviews.")
     * )
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
     * @OA\Delete(
     * path="/api/reviews/{review}",
     * operationId="deleteReview",
     * tags={"Reviews"},
     * summary="Delete a review",
     * description="Authors can delete their own reviews. Executives can delete any review.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="review",
     * in="path",
     * description="Review ID",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Review deleted",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Review deleted")
     * )
     * )
     * )
     */
    public function destroy(Review $review)
    {
        Gate::authorize('delete', $review);

        $review->delete();

        return response()->json(['message' => 'Review deleted']);
    }
}
