<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 * schema="ProjectResource",
 * title="Project Resource",
 * description="Details of a project including metrics, assigned teams, and advisors.",
 * @OA\Property(property="id", type="integer", example=101, description="Unique Project ID"),
 * @OA\Property(property="name", type="string", example="Q3 Marketing Campaign", description="Project Name"),
 * @OA\Property(property="description", type="string", example="Revamping the core legacy system.", nullable=true),
 * @OA\Property(
 * property="status", 
 * type="string", 
 * example="active", 
 * enum={"active", "completed", "archived", "hold"},
 * description="Current status of the project"
 * ),
 * @OA\Property(
 * property="metrics",
 * type="object",
 * description="Aggregated review statistics",
 * @OA\Property(property="average_rating", type="number", format="float", example=4.5),
 * @OA\Property(property="review_count", type="integer", example=12)
 * ),
 * @OA\Property(
 * property="created_by",
 * ref="#/components/schemas/UserResource",
 * description="The Executive who created the project (nullable if user deleted)",
 * nullable=true
 * ),
 * @OA\Property(
 * property="teams",
 * type="array",
 * description="List of teams assigned to this project",
 * @OA\Items(ref="#/components/schemas/TeamResource")
 * ),
 * @OA\Property(
 * property="advisors",
 * type="array",
 * description="List of internal advisors assigned to this project",
 * @OA\Items(ref="#/components/schemas/UserResource")
 * ),
 * @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-01T12:00:00Z"),
 * @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example="2023-12-31T23:59:59Z")
 * )
 */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->project_id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,

            'metrics' => [
                'average_rating' => $this->average_rating,
                'review_count' => $this->review_count,
            ],

            'created_by' => new UserResource($this->whenLoaded('creator')),
            'teams' => TeamResource::collection($this->whenLoaded('teams')),
            'advisors' => UserResource::collection($this->whenLoaded('advisors')),
            'created_at' => $this->created_at->toIso8601String(),
            'deleted_at' => $this->when($this->deleted_at, fn() => $this->deleted_at),
        ];
    }
}
