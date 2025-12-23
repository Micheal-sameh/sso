<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManageGroupMembersRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ];
    }
}
