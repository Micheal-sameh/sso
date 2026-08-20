<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Guard explicitly set to "api": the default `current_password` rule
            // checks the default web guard, which has no user on a stateless call.
            'current_password' => ['required', 'current_password:api'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'code' => ['required', 'string'],
        ];
    }
}
