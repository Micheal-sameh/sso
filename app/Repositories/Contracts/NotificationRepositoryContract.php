<?php

namespace App\Repositories\Contracts;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface NotificationRepositoryContract
{
    public function create(array $data): Notification;

    public function syncStageTargets(Notification $notification, array $stageIds): void;

    public function syncGroupTargets(Notification $notification, array $groupIds): void;

    public function syncUserTargets(Notification $notification, array $userIds): void;

    public function paginateAll(int $perPage = 20): LengthAwarePaginator;

    public function paginateForUser(User $user, int $perPage = 20): LengthAwarePaginator;

    /**
     * @return Collection<int, Notification>
     */
    public function recentForUser(User $user, int $limit = 5): Collection;

    public function markAsRead(Notification $notification, User $user): void;

    public function isAddressedTo(Notification $notification, User $user): bool;

    public function unreadCountForUser(User $user): int;

    public function delete(Notification $notification): bool;
}
