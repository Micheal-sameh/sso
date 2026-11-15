<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ExternalUserSyncRequest extends FormRequest
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
            'membership_code' => ['required', 'string', 'max:255'],
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'date_of_birth' => ['sometimes', 'nullable', 'date', 'before:today'],
            'is_active' => ['sometimes', 'boolean'],
            'stage' => ['sometimes', 'nullable', 'string', 'max:255'],
            'stage_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'groups' => ['sometimes', 'array', 'max:20'],
            'groups.*' => ['string', 'max:255', 'distinct'],
        ];
    }
}
