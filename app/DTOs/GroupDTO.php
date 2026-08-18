<?php

namespace App\DTOs;

use Illuminate\Http\Request;

readonly class GroupDTO
{
    public function __construct(
        public string $name,
        public ?string $description,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->string('name')->toString(),
            description: $request->string('description')->toString() ?: null,
        );
    }
}
