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
        ];
    }
}
