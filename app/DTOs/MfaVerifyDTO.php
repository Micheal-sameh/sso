<?php

namespace App\DTOs;

use Illuminate\Http\Request;

readonly class MfaVerifyDTO
{
    public function __construct(
        public string $code,
        public int $userId,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            code: $request->string('code')->toString(),
            userId: (int) $request->session()->get('mfa_user_id'),
        );
    }
}
