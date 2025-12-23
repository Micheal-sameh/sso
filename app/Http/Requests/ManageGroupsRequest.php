<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManageGroupsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'exists:groups,id',
        ];
    }
}
