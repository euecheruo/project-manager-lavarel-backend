<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 * schema="UserResource",
 * title="User Resource",
 * description="Employee details including global roles and context-specific pivot data.",
 * @OA\Property(property="id", type="integer", example=101, description="Unique User ID"),
 * @OA\Property(property="first_name", type="string", example="Alice"),
 * @OA\Property(property="last_name", type="string", example="Smith"),
 * @OA\Property(property="full_name", type="string", example="Alice Smith", readOnly=true, description="Computed full name attribute"),
 * @OA\Property(property="email", type="string", format="email", example="alice@company.com"),
 * @OA\Property(property="is_active", type="boolean", example=true),
 * @OA\Property(
 * property="roles",
 * type="array",
 * description="List of assigned global roles (Executive, Manager, Associate)",
 * @OA\Items(type="string", example="Associate")
 * ),
 * @OA\Property(
 * property="joined_team_at",
 * type="string",
 * format="date-time",
 * nullable=true,
 * description="Timestamp when user joined the team. Only present in Team Roster views."
 * ),
 * @OA\Property(
 * property="assigned_advisor_at",
 * type="string",
 * format="date-time",
 * nullable=true,
 * description="Timestamp when user was assigned as an advisor. Only present in Project Advisor contexts."
 * )
 * )
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->user_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'roles' => $this->roles->pluck('role_name'),
            'joined_team_at' => $this->whenPivotLoaded('team_members', function () {
                return $this->pivot->joined_at;
            }),
            'assigned_advisor_at' => $this->whenPivotLoaded('project_advisors', function () {
                return $this->pivot->assigned_at;
            }),
        ];
    }
}
