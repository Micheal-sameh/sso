<?php

namespace App\DTOs;

use Illuminate\Http\Request;

readonly class OAuthClientDTO
{
    public function __construct(
        public string $name,
        public string $redirect,
        public ?string $homepageUrl,
        public ?string $appLogoUrl,
        public bool $isInternal,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->string('name')->toString(),
            redirect: $request->string('redirect')->toString(),
            homepageUrl: $request->string('homepage_url')->toString() ?: null,
            appLogoUrl: $request->string('app_logo_url')->toString() ?: null,
            isInternal: $request->boolean('is_internal'),
        );
    }
}
