<?php

namespace App\Http\Requests\Assignments;

use Illuminate\Foundation\Http\FormRequest;

class AssignAdvisorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('projects.assign_advisors');
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,project_id'],
            'user_id' => ['required', 'integer', 'exists:users,user_id'],
        ];
    }
}
