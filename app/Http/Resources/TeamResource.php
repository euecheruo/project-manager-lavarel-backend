<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 * schema="TeamResource",
 * title="Team Resource",
 * description="Represents a team within the organization.",
 * @OA\Property(property="id", type="integer", example=10, description="Unique identifier for the team"),
 * @OA\Property(property="name", type="string", example="Frontend Alpha", description="Name of the team"),
 * @OA\Property(
 * property="member_count",
 * type="integer",
 * example=5,
 * nullable=true,
 * description="Total number of members in the team (Conditional)"
 * ),
 * @OA\Property(
 * property="members",
 * type="array",
 * description="List of team members (Conditional: only when roster is loaded)",
 * @OA\Items(ref="#/components/schemas/UserResource"),
 * nullable=true
 * ),
 * @OA\Property(
 * property="created_at",
 * type="string",
 * format="date-time",
 * example="2024-01-01T12:00:00Z",
 * description="Timestamp when the team was created"
 * )
 * )
 */
class TeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->team_id,
            'name' => $this->name,
            'member_count' => $this->whenCounted('members'),
            'members' => UserResource::collection($this->whenLoaded('members')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
