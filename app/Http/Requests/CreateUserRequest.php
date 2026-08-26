<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'membership_code' => ['required', 'string', 'max:255', 'unique:users,membership_code'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:superadmin,admin,secretary,user'],
            'stage_id' => ['required', 'integer', 'exists:stages,id'],
        ];
    }
}
