<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MfaVerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'regex:/^(\d{6}|[A-Z0-9]{4}-[A-Z0-9]{4})$/i'],
        ];
    }
}
