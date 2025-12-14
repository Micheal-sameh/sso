<?php

namespace App\Http\Requests;

use App\Models\SSOUser;
use Illuminate\Foundation\Http\FormRequest;

class StoreSSOUserRequest extends FormRequest
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
            'user_id' => 'required|integer|exists:users,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $existingRecord = SSOUser::where('user_id', $this->user_id)
                ->first();

            if ($existingRecord) {
                $validator->errors()->add('duplicate', 'SSO record for this User is already exists.');
            }
        });
    }
}
