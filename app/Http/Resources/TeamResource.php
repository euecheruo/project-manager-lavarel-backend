<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
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
