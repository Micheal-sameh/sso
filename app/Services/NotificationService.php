<?php

namespace App\Services;

use App\DTOs\NotificationDTO;
use App\Enums\NotificationTargetType;
use App\Events\NotificationSent;
use App\Models\Notification;
use App\Models\User;
use App\Repositories\Contracts\NotificationRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NotificationService
{
    public function __construct(
        protected NotificationRepositoryContract $notificationRepository,
    ) {}

    /**
     * Send a new notification to its target audience.
     */
    public function send(NotificationDTO $dto, ?User $sender): Notification
    {
        $notification = $this->notificationRepository->create([
            'message' => $dto->message,
            'target_type' => $dto->targetType,
            'created_by' => $sender?->id,
        ]);

        if ($dto->image !== null) {
            $notification->addMedia($dto->image)->toMediaCollection('image');
        }

        match ($dto->targetType) {
            NotificationTargetType::Stage => $this->notificationRepository->syncStageTargets($notification, $dto->stageIds),
            NotificationTargetType::Group => $this->notificationRepository->syncGroupTargets($notification, $dto->groupIds),
            NotificationTargetType::Specific => $this->notificationRepository->syncUserTargets($notification, $dto->userIds),
            NotificationTargetType::All => null,
        };

        $recipientIds = $this->notificationRepository->recipientUserIds($notification);

        if ($recipientIds->isNotEmpty()) {
            broadcast(new NotificationSent($recipientIds->all(), $this->toRecipientArray($notification)));
        }

        return $notification;
    }

    /**
     * Paginate every notification ever sent, for the admin "Sent" list.
     */
    public function listAllPaginated(int $perPage = 20): LengthAwarePaginator
    {
        return $this->notificationRepository->paginateAll($perPage);
    }

    /**
     * Paginate the notifications addressed to the given user.
     */
    public function listForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $this->notificationRepository->paginateForUser($user, $perPage);
    }

    /**
     * The most recent notifications addressed to the given user, for the bell dropdown.
     *
     * @return Collection<int, Notification>
     */
    public function recentForUser(User $user, int $limit = 5): Collection
    {
        return $this->notificationRepository->recentForUser($user, $limit);
    }

    /**
     * Mark a notification as read by the given user.
     */
    public function markAsRead(Notification $notification, User $user): void
    {
        $this->notificationRepository->markAsRead($notification, $user);
    }

    /**
     * Whether the given notification is addressed to the given user.
     */
    public function isAddressedTo(Notification $notification, User $user): bool
    {
        return $this->notificationRepository->isAddressedTo($notification, $user);
    }

    /**
     * Count notifications addressed to the given user that they have not yet read.
     */
    public function unreadCount(User $user): int
    {
        return $this->notificationRepository->unreadCountForUser($user);
    }

    /**
     * Delete (retract) a sent notification.
     */
    public function delete(Notification $notification): void
    {
        $this->notificationRepository->delete($notification);
    }

    /**
     * Shape a notification for a recipient's own view (inbox list or bell dropdown).
     *
     * @return array{id: int, message: string, image_url: ?string, is_read: bool, created_at: string}
     */
    public function toRecipientArray(Notification $notification): array
    {
        return [
            'id' => $notification->id,
            'message' => $notification->message,
            'image_url' => $notification->image_url,
            'is_read' => (bool) $notification->is_read,
            'created_at' => $notification->created_at->diffForHumans(),
        ];
    }
}
