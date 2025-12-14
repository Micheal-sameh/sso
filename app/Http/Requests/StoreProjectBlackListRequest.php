<?php

namespace App\Http\Requests;

use App\Models\ProjectBlackList;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectBlackListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'user_id' => 'required|exists:users,id',
            'reason' => 'nullable|string',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $existingRecord = ProjectBlackList::where('project_id', $this->project_id)
                ->where('user_id', $this->user_id)
                ->first();

            if ($existingRecord) {
                $validator->errors()->add('duplicate', 'A black list record for this project and user already exists.');
            }
        });
    }
}
