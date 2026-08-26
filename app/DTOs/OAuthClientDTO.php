<?php

namespace App\DTOs;

use App\Enums\ClientAccessMode;
use App\Enums\ClientGrantType;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class OAuthClientDTO
{
    /**
     * @param  int[]  $stageIds
     * @param  int[]  $userIds
     * @param  int[]  $groupIds
     * @param  int[]  $excludedUserIds
     */
    public function __construct(
        public string $name,
        public string $redirect,
        public ?string $homepageUrl,
        public ?string $logoutUri,
        public ?UploadedFile $appLogo,
        public bool $isInternal,
        public bool $isPublicClient,
        public ClientGrantType $grantType,
        public ClientAccessMode $accessMode,
        public array $stageIds,
        public array $userIds,
        public array $groupIds,
        public array $excludedUserIds,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $appLogo = $request->file('app_logo');

        return new self(
            name: $request->string('name')->toString(),
            redirect: $request->string('redirect')->toString(),
            homepageUrl: $request->string('homepage_url')->toString() ?: null,
            logoutUri: $request->string('logout_uri')->toString() ?: null,
            appLogo: $appLogo instanceof UploadedFile ? $appLogo : null,
            isInternal: $request->boolean('is_internal'),
            isPublicClient: $request->boolean('is_public_client'),
            grantType: ClientGrantType::from($request->string('grant_type')->toString() ?: ClientGrantType::AuthorizationCode->value),
            accessMode: ClientAccessMode::from($request->string('access_mode')->toString()),
            stageIds: array_map('intval', $request->array('stage_ids')),
            userIds: array_map('intval', $request->array('user_ids')),
            groupIds: array_map('intval', $request->array('group_ids')),
            excludedUserIds: array_map('intval', $request->array('excluded_user_ids')),
        );
    }
}
