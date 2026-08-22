<?php

namespace App\Repositories\Eloquent;

use App\Enums\NotificationTargetType;
use App\Models\Notification;
use App\Models\User;
use App\Repositories\Contracts\NotificationRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NotificationRepository implements NotificationRepositoryContract
{
    /**
     * Create a new notification.
     */
    public function create(array $data): Notification
    {
        return Notification::create($data);
    }

    /**
     * Replace the set of stages this notification was sent to.
     */
    public function syncStageTargets(Notification $notification, array $stageIds): void
    {
        $notification->stages()->sync($stageIds);
    }

    /**
     * Replace the set of groups this notification was sent to.
     */
    public function syncGroupTargets(Notification $notification, array $groupIds): void
    {
        $notification->groups()->sync($groupIds);
    }

    /**
     * Replace the set of individual users this notification was sent to.
     */
    public function syncUserTargets(Notification $notification, array $userIds): void
    {
        $notification->targetUsers()->sync($userIds);
    }

    /**
     * Paginate every notification ever sent, for the admin "Sent" list.
     *
     * @param  array{type?: string}  $filters
     */
    public function paginateAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->applyFilters(Notification::with(['creator', 'stages', 'groups', 'targetUsers']), $filters)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Paginate the notifications addressed to the given user (by direct
     * targeting, their stage, or any of their groups), newest first, each
     * annotated with whether that user has read it.
     *
     * @param  array{type?: string}  $filters
     */
    public function paginateForUser(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->applyFilters($this->scopeForUser(Notification::query(), $user), $filters)
            ->withCount(['readers as is_read' => fn (Builder $query) => $query->where('users.id', $user->id)])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{type?: string}  $filters
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query->when($filters['type'] ?? null, fn (Builder $q, string $type) => $q->where('type', $type));
    }

    /**
     * The most recent notifications addressed to the given user, each
     * annotated with whether that user has read it. Used for the bell dropdown.
     *
     * @return Collection<int, Notification>
     */
    public function recentForUser(User $user, int $limit = 5): Collection
    {
        return $this->scopeForUser(Notification::query(), $user)
            ->withCount(['readers as is_read' => fn (Builder $query) => $query->where('users.id', $user->id)])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Mark a notification as read by the given user (idempotent).
     */
    public function markAsRead(Notification $notification, User $user): void
    {
        $notification->readers()->syncWithoutDetaching([
            $user->id => ['read_at' => now()],
        ]);
    }

    /**
     * Mark every notification addressed to the given user as read (idempotent).
     */
    public function markAllAsRead(User $user): void
    {
        $unreadIds = $this->scopeForUser(Notification::query(), $user)
            ->whereDoesntHave('readers', fn (Builder $query) => $query->where('users.id', $user->id))
            ->pluck('id');

        $this->upsertReads($unreadIds, $user);
    }

    /**
     * Mark the given notifications as read by the user, ignoring any ids not
     * actually addressed to them (idempotent).
     */
    public function markManyAsRead(Collection $notificationIds, User $user): void
    {
        $ids = $this->scopeForUser(Notification::query(), $user)
            ->whereIn('id', $notificationIds)
            ->pluck('id');

        $this->upsertReads($ids, $user);
    }

    /**
     * Bulk-upsert read receipts for the given notification ids and user.
     */
    private function upsertReads(Collection $notificationIds, User $user): void
    {
        if ($notificationIds->isEmpty()) {
            return;
        }

        $now = now();

        DB::table('notification_reads')->upsert(
            $notificationIds->map(fn (int $id) => [
                'notification_id' => $id,
                'user_id' => $user->id,
                'read_at' => $now,
            ])->all(),
            ['notification_id', 'user_id'],
            ['read_at'],
        );
    }

    /**
     * Whether the given notification is addressed to the given user.
     */
    public function isAddressedTo(Notification $notification, User $user): bool
    {
        return $this->scopeForUser(Notification::query(), $user)
            ->where('id', $notification->id)
            ->exists();
    }

    /**
     * Count notifications addressed to the given user that they have not yet read.
     */
    public function unreadCountForUser(User $user): int
    {
        return $this->scopeForUser(Notification::query(), $user)
            ->whereDoesntHave('readers', fn (Builder $query) => $query->where('users.id', $user->id))
            ->count();
    }

    /**
     * Delete a notification (and its target/read pivot rows, via cascade).
     */
    public function delete(Notification $notification): bool
    {
        return (bool) $notification->delete();
    }

    /**
     * IDs of every user this notification is addressed to.
     *
     * @return Collection<int, int>
     */
    public function recipientUserIds(Notification $notification): Collection
    {
        return match ($notification->target_type) {
            NotificationTargetType::All => User::query()->pluck('id'),
            NotificationTargetType::Stage => User::query()
                ->whereHas('stage', fn (Builder $query) => $query->whereIn(
                    'stages.id',
                    $notification->stages()->pluck('stages.id'),
                ))
                ->pluck('id'),
            NotificationTargetType::Group => User::query()
                ->whereHas('groups', fn (Builder $query) => $query->whereIn(
                    'groups.id',
                    $notification->groups()->pluck('groups.id'),
                ))
                ->pluck('id'),
            NotificationTargetType::Specific => $notification->targetUsers()->pluck('users.id'),
        };
    }

    /**
     * Constrain a notifications query to those addressed to the given user,
     * whether by "all users", their stage, any of their groups, or by name.
     */
    private function scopeForUser(Builder $query, User $user): Builder
    {
        $user->loadMissing('groups');
        $groupIds = $user->groups->pluck('id');

        return $query->where(function (Builder $q) use ($user, $groupIds) {
            $q->where('target_type', NotificationTargetType::All)
                ->orWhere(function (Builder $q2) use ($user) {
                    $q2->where('target_type', NotificationTargetType::Stage)
                        ->whereHas('stages', fn (Builder $q3) => $q3->where('stages.id', $user->stage_id));
                })
                ->orWhere(function (Builder $q2) use ($groupIds) {
                    $q2->where('target_type', NotificationTargetType::Group)
                        ->whereHas('groups', fn (Builder $q3) => $q3->whereIn('groups.id', $groupIds));
                })
                ->orWhere(function (Builder $q2) use ($user) {
                    $q2->where('target_type', NotificationTargetType::Specific)
                        ->whereHas('targetUsers', fn (Builder $q3) => $q3->where('users.id', $user->id));
                });
        });
    }
}
