<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ClientPasswordResetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'string'],
            'client_secret' => ['required', 'string'],
            'email' => ['required_without:membership_code', 'nullable', 'email'],
            'membership_code' => ['required_without:email', 'nullable', 'string'],
        ];
    }
}
