<?php

namespace App\DTOs;

use App\Enums\UserRole;
use Illuminate\Http\Request;

readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $membershipCode,
        public string $password,
        public UserRole $role,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
            membershipCode: $request->string('membership_code')->toString(),
            password: $request->string('password')->toString(),
            role: UserRole::from($request->string('role')->toString()),
        );
    }
}
