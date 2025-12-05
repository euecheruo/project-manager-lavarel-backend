<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('projects.update');
    }

    public function rules(): array
    {
        $projectId = $this->route('project')?->project_id;

        return [
            'name' => ['sometimes', 'string', 'max:150', "unique:projects,name,{$projectId},project_id"],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:active,hold,completed,archived'],
        ];
    }
}
