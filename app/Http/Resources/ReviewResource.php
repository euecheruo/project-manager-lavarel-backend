<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
