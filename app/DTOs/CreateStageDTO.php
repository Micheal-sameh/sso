<?php

namespace App\DTOs;

use Illuminate\Http\Request;

readonly class CreateStageDTO
{
    public function __construct(
        public string $name,
        public string $code,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->string('name')->toString(),
            code: $request->string('code')->toString(),
        );
    }
}
