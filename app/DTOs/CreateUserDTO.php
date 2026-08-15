<?php

namespace App\DTOs;

use App\Enums\UserRole;
use Illuminate\Http\Request;

readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone,
        public string $membershipCode,
        public string $password,
        public UserRole $role,
        public int $stageId,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
            phone: $request->string('phone')->toString() ?: null,
            membershipCode: $request->string('membership_code')->toString(),
            password: $request->string('password')->toString(),
            role: UserRole::from($request->string('role')->toString()),
            stageId: $request->integer('stage_id'),
        );
    }
}
