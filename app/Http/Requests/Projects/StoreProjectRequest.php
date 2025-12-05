<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('projects.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', 'unique:projects,name'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,hold,completed'],
        ];
    }
}
