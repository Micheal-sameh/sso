<?php

namespace App\Http\Requests;

use App\Enums\DonationGateway;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fund_id' => ['required', 'integer', Rule::exists('funds', 'id')->where('is_active', true)],
            'amount' => ['required', 'numeric', 'min:10', 'max:1000000'],
            'gateway' => ['required', Rule::enum(DonationGateway::class)],
            'is_recurring' => ['sometimes', 'boolean'],
        ];
    }
}
