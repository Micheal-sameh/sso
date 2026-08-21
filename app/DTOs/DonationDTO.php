<?php

namespace App\DTOs;

use App\Enums\DonationGateway;
use App\Models\User;
use Illuminate\Http\Request;

readonly class DonationDTO
{
    public function __construct(
        public int $fundId,
        public int $userId,
        public float $amount,
        public DonationGateway $gateway,
        public bool $isRecurring,
    ) {}

    public static function fromRequest(Request $request, User $user): self
    {
        return new self(
            fundId: (int) $request->input('fund_id'),
            userId: $user->id,
            amount: (float) $request->input('amount'),
            gateway: DonationGateway::from($request->string('gateway')->toString()),
            isRecurring: $request->boolean('is_recurring'),
        );
    }
}
