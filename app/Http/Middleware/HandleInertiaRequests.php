<?php

namespace App\Http\Middleware;

use App\DTOs\UserDTO;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $notificationService = app(NotificationService::class);
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? UserDTO::fromModel($user)->toArray() : null,
            ],
            'unread_notifications_count' => $user ? $notificationService->unreadCount($user) : 0,
            'recent_notifications' => $user
                ? $notificationService->recentForUser($user)->map(
                    fn ($notification) => $notificationService->toRecipientArray($notification),
                )->values()
                : [],
            'flash' => [
                'type' => $request->session()->get('flash.type'),
                'message' => $request->session()->get('flash.message'),
            ],
            'csrf_token' => csrf_token(),
            'errors' => $request->session()->get('errors')
                ? $request->session()->get('errors')->getBag('default')->getMessages()
                : [],
        ];
    }
}
