<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QrLoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'qr_code' => 'required|string',
        ];
    }
}
