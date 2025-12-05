<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
