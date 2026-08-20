<?php

namespace App\DTOs;

use App\Enums\NotificationTargetType;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

readonly class NotificationDTO
{
    /**
     * @param  int[]  $stageIds
     * @param  int[]  $groupIds
     * @param  int[]  $userIds
     */
    public function __construct(
        public string $message,
        public NotificationTargetType $targetType,
        public array $stageIds,
        public array $groupIds,
        public array $userIds,
        public UploadedFile|string|null $image,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $image = $request->file('image');

        return new self(
            message: $request->string('message')->toString(),
            targetType: NotificationTargetType::from($request->string('target_type')->toString()),
            stageIds: array_map('intval', $request->array('stage_ids')),
            groupIds: array_map('intval', $request->array('group_ids')),
            userIds: array_map('intval', $request->array('user_ids')),
            image: $image instanceof UploadedFile ? $image : null,
        );
    }
}
