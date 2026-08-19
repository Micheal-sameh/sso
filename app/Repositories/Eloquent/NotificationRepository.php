<?php

namespace App\Repositories\Eloquent;

use App\Enums\NotificationTargetType;
use App\Models\Notification;
use App\Models\User;
use App\Repositories\Contracts\NotificationRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
     */
    public function paginateAll(int $perPage = 20): LengthAwarePaginator
    {
        return Notification::with(['creator', 'stages', 'groups', 'targetUsers'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Paginate the notifications addressed to the given user (by direct
     * targeting, their stage, or any of their groups), newest first, each
     * annotated with whether that user has read it.
     */
    public function paginateForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $this->scopeForUser(Notification::query(), $user)
            ->withCount(['readers as is_read' => fn (Builder $query) => $query->where('users.id', $user->id)])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
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
