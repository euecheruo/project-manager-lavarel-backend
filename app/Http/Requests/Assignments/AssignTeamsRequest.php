<?php

namespace App\Http\Requests\Assignments;

use Illuminate\Foundation\Http\FormRequest;

class AssignTeamsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('projects.assign_teams');
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,project_id'],
            'team_ids' => ['required', 'array'],
            'team_ids.*' => ['integer', 'exists:teams,team_id'],
        ];
    }
}
