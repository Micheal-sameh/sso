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
            // Laravel's built-in "url" rule only accepts a fixed whitelist of
            // known URI schemes, which excludes bespoke reverse-DNS mobile
            // app schemes (e.g. "com.avarewase.passport://callback") used by
            // native OAuth clients (RFC 8252). Accept any syntactically valid
            // "scheme://..." URI instead, in addition to normal http(s) URLs.
            'redirect' => ['required', 'string', 'max:2048', 'regex:/^[a-zA-Z][a-zA-Z0-9+.-]*:\/\/\S+$/'],
            'homepage_url' => ['nullable', 'url'],
            'app_logo' => ['nullable', 'image:allow_svg', 'max:2048'],
            'is_internal' => ['boolean'],
            'is_public_client' => ['boolean'],
            'grant_type' => ['nullable', 'in:authorization_code,implicit'],
            'access_mode' => ['required', 'in:all,stage,specific'],
            'stage_ids' => ['required_if:access_mode,stage', 'array'],
            'stage_ids.*' => ['integer', 'exists:stages,id'],
            'user_ids' => ['required_if:access_mode,specific', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ];
    }
}
