<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'membership_code' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'fcm_token' => 'nullable|string',
            'app_name' => 'required|string',
        ];
    }
}
