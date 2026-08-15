<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OAuthClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'redirect' => ['required', 'url'],
            'homepage_url' => ['nullable', 'url'],
            'app_logo_url' => ['nullable', 'url'],
            'is_internal' => ['boolean'],
            'access_mode' => ['required', 'in:all,stage,specific'],
            'stage_ids' => ['required_if:access_mode,stage', 'array'],
            'stage_ids.*' => ['integer', 'exists:stages,id'],
            'user_ids' => ['required_if:access_mode,specific', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ];
    }
}
