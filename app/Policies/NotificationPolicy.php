<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;

class NotificationPolicy
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    /**
     * A user may only view/act on a notification that is actually addressed
     * to them — prevents reading another user's notification by guessing its
     * id in the URL.
     */
    public function view(User $user, Notification $notification): bool
    {
        return $this->notificationService->isAddressedTo($notification, $user);
    }
}
