<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 * schema="ReviewResource",
 * title="Review Resource",
 * description="Review details including rating, content, and reviewer information (masked if unauthorized).",
 * @OA\Property(property="id", type="integer", example=1, description="Unique Review ID"),
 * @OA\Property(property="rating", type="integer", example=5, minimum=1, maximum=5, description="Rating from 1-5"),
 * @OA\Property(property="content", type="string", example="Great work on the backend modules.", description="Review content"),
 * @OA\Property(
 * property="reviewer",
 * type="object",
 * description="Reviewer details. Name is masked for non-executives unless it's their own review.",
 * @OA\Property(property="id", type="integer", nullable=true, example=10, description="User ID (null if anonymous)"),
 * @OA\Property(property="name", type="string", example="John Doe", description="Full Name (or 'Anonymous Team Member')"),
 * @OA\Property(property="role", type="string", example="Manager", description="Role name (or 'Hidden')")
 * ),
 * @OA\Property(property="project_id", type="integer", example=101),
 * @OA\Property(property="project_name", type="string", example="Q3 Marketing Campaign", nullable=true),
 * @OA\Property(property="is_mine", type="boolean", example=true, description="True if the current user wrote this review"),
 * @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
 * @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-01T12:00:00Z")
 * )
 */
class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Determine Visibility: Executives or the author can see the name
        $canSeeName = $user->hasPermission('reviews.view_names') || $user->user_id === $this->reviewer_id;

        return [
            'id' => $this->review_id,
            'rating' => $this->rating,
            'content' => $this->content,
            'reviewer' => $this->when($canSeeName, function () {
                return [
                    'id' => $this->reviewer->user_id,
                    'name' => $this->reviewer->full_name,
                    'role' => $this->reviewer->roles->pluck('role_name')->first() ?? 'Staff',
                ];
            }, [
                'id' => null,
                'name' => 'Anonymous Team Member',
                'role' => 'Hidden',
            ]),
            'project_id' => $this->project_id,
            'project_name' => $this->whenLoaded('project', fn() => $this->project->name),
            'is_mine' => $user->user_id === $this->reviewer_id,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
