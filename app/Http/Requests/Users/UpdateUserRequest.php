<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.update');
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->user_id;

        return [
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'email', "unique:users,email,{$userId},user_id"],
            'role_name' => ['sometimes', 'string', 'exists:roles,role_name'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
