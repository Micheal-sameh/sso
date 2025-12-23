<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUserUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'action' => 'required|string|in:activate,deactivate,delete',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:users,id',
        ];
    }
}
