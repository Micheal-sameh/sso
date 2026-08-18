<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class MfaLoginVerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mfa_token' => ['required', 'string'],
            'code' => ['required', 'string'],
        ];
    }
}
