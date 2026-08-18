<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $group = $this->route('group');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('groups', 'name')->ignore($group?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
