<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:4096'],
            'target_type' => ['required', 'in:all,stage,specific,group'],
            'stage_ids' => ['required_if:target_type,stage', 'array'],
            'stage_ids.*' => ['integer', 'exists:stages,id'],
            'user_ids' => ['required_if:target_type,specific', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'group_ids' => ['required_if:target_type,group', 'array'],
            'group_ids.*' => ['integer', 'exists:groups,id'],
        ];
    }
}
