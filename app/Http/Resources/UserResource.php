<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
