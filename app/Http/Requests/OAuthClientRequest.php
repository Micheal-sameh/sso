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
            'redirect' => ['required', 'string', 'max:2048', 'regex:/^[a-zA-Z][a-zA-Z0-9+.-]*:\/\/\S+$/', function ($attribute, $value, $fail) {
                $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
                $host = parse_url($value, PHP_URL_HOST);

                if (in_array($scheme, ['javascript', 'data', 'vbscript', 'file', 'blob', 'about'], true)) {
                    $fail('This redirect URI scheme is not allowed.');
                } elseif ($scheme === 'http' && ! in_array($host, ['localhost', '127.0.0.1'], true)) {
                    $fail('Redirect URIs must use https:// (http is only allowed for localhost).');
                } elseif (str_contains($value, '#')) {
                    $fail('Redirect URIs must not contain a fragment.');
                }
            }],
            'homepage_url' => ['nullable', 'url:http,https'],
            // https-only: this URL receives a signed webhook carrying a
            // user's OIDC sub over plain HTTP would let it be tampered
            // with in transit. localhost/127.0.0.1 are exempted so local
            // development against a client app running without TLS still works.
            'logout_uri' => ['nullable', 'url:http,https', function ($attribute, $value, $fail) {
                $host = parse_url($value, PHP_URL_HOST);

                if (! str_starts_with($value, 'https://') && ! in_array($host, ['localhost', '127.0.0.1'], true)) {
                    $fail('The logout webhook URL must use https://.');
                }
            }],
            'app_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_internal' => ['boolean'],
            'is_public_client' => ['boolean'],
            'grant_type' => ['nullable', 'in:'.(config('passport.implicit_grant') ? 'authorization_code,implicit' : 'authorization_code')],
            'access_mode' => ['required', 'in:all,stage,specific,group,all_except_users'],
            'stage_ids' => ['required_if:access_mode,stage', 'array'],
            'stage_ids.*' => ['integer', 'exists:stages,id'],
            'user_ids' => ['required_if:access_mode,specific', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'group_ids' => ['required_if:access_mode,group', 'array'],
            'group_ids.*' => ['integer', 'exists:groups,id'],
            'excluded_user_ids' => ['required_if:access_mode,all_except_users', 'array'],
            'excluded_user_ids.*' => ['integer', 'exists:users,id'],
        ];
    }
}
