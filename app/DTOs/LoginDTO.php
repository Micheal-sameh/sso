<?php

namespace App\DTOs;

use Illuminate\Http\Request;

readonly class LoginDTO
{
    public function __construct(
        public string $membershipCode,
        public string $password,
        public bool $remember,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            membershipCode: $request->string('membership_code')->toString(),
            password: $request->string('password')->toString(),
            remember: $request->boolean('remember'),
        );
    }
}
