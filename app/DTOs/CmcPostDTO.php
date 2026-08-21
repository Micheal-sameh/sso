<?php

namespace App\DTOs;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class CmcPostDTO
{
    public function __construct(
        public ?string $text,
        public ?string $url,
        public UploadedFile|string|null $image,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $image = $request->file('image');

        return new self(
            text: $request->filled('text') ? $request->string('text')->toString() : null,
            url: $request->filled('url') ? $request->string('url')->toString() : null,
            image: $image instanceof UploadedFile ? $image : null,
        );
    }
}
