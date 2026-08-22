<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\BaseController;
use App\Http\Traits\HandlesInertiaResponses;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class NotificationController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['type']);

        $notifications = $this->notificationService->listForUser(Auth::user(), $filters)
            ->through(fn (Notification $notification) => $this->notificationService->toRecipientArray($notification));

        return $this->inertia('Account/Notifications', [
            'notifications' => $notifications,
            'filters' => $filters,
            'availableTypes' => $this->notificationService->availableTypesForUser(Auth::user()),
        ]);
    }

    public function markRead(Notification $notification): RedirectResponse
    {
        $this->notificationService->markAsRead($notification, Auth::user());

        return $this->redirectBack(null, 'success');
    }

    public function markAllRead(): RedirectResponse
    {
        $this->notificationService->markAllAsRead(Auth::user());

        return $this->redirectBack(null, 'success');
    }

    public function markManyRead(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $this->notificationService->markManyAsRead($validated['ids'], Auth::user());

        return $this->redirectBack(null, 'success');
    }
}
