<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'membership_code' => ['required', 'string', 'max:255', Rule::unique('users', 'membership_code')->ignore($user->id)],
            'role' => ['required', 'in:superadmin,admin,user'],
            'stage_id' => ['required', 'integer', 'exists:stages,id'],
        ];
    }
}
