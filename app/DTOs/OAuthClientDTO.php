<?php

namespace App\DTOs;

use App\Enums\ClientAccessMode;
use Illuminate\Http\Request;

readonly class OAuthClientDTO
{
    /**
     * @param  int[]  $stageIds
     * @param  int[]  $userIds
     */
    public function __construct(
        public string $name,
        public string $redirect,
        public ?string $homepageUrl,
        public ?string $appLogoUrl,
        public bool $isInternal,
        public ClientAccessMode $accessMode,
        public array $stageIds,
        public array $userIds,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->string('name')->toString(),
            redirect: $request->string('redirect')->toString(),
            homepageUrl: $request->string('homepage_url')->toString() ?: null,
            appLogoUrl: $request->string('app_logo_url')->toString() ?: null,
            isInternal: $request->boolean('is_internal'),
            accessMode: ClientAccessMode::from($request->string('access_mode')->toString()),
            stageIds: array_map('intval', $request->array('stage_ids')),
            userIds: array_map('intval', $request->array('user_ids')),
        );
    }
}
