<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CmcPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => ['nullable', 'string', 'max:5000', 'required_without_all:url,image'],
            'url' => ['nullable', 'url', 'max:2048'],
            'image' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
