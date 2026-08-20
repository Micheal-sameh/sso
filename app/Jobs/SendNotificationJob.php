<?php

namespace App\Jobs;

use App\DTOs\NotificationDTO;
use App\Enums\NotificationTargetType;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  int[]  $stageIds
     * @param  int[]  $groupIds
     * @param  int[]  $userIds
     * @param  string|null  $imagePath  path (on the "local" disk) of the image temporarily stored by the controller
     */
    public function __construct(
        protected string $message,
        protected NotificationTargetType $targetType,
        protected array $stageIds,
        protected array $groupIds,
        protected array $userIds,
        protected ?int $senderId,
        protected ?string $imagePath,
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        $dto = new NotificationDTO(
            message: $this->message,
            targetType: $this->targetType,
            stageIds: $this->stageIds,
            groupIds: $this->groupIds,
            userIds: $this->userIds,
            image: $this->imagePath ? Storage::path($this->imagePath) : null,
        );

        $notificationService->send($dto, $this->senderId ? User::find($this->senderId) : null);

        if ($this->imagePath) {
            Storage::delete($this->imagePath);
        }
    }
}
