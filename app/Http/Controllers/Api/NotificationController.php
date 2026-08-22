<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Notifications')]
class NotificationController extends BaseController
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    #[OA\Get(
        path: '/api/notifications',
        summary: 'List notifications addressed to the current user (paginated)',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                new OA\Schema(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Notification')),
                ]),
            ])),
        ],
    )]
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->notificationService->listForUser($request->user())
                ->through(fn (Notification $notification) => $this->notificationService->toRecipientArray($notification))
        );
    }

    #[OA\Post(
        path: '/api/notifications/{notification}/read',
        summary: 'Mark one notification as read',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'notification', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Marked as read.', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Not found'),
        ],
    )]
    public function markRead(Request $request, Notification $notification): JsonResponse
    {
        $this->notificationService->markAsRead($notification, $request->user());

        return response()->json(['message' => __('messages.notification_marked_read')]);
    }

    #[OA\Post(
        path: '/api/notifications/read-all',
        summary: 'Mark every notification as read',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(response: 200, description: 'All marked as read.', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function markAllRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllAsRead($request->user());

        return response()->json(['message' => __('messages.all_notifications_marked_read')]);
    }

    #[OA\Post(
        path: '/api/notifications/read-many',
        summary: 'Mark specific notifications as read',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['ids'],
            properties: [
                new OA\Property(property: 'ids', type: 'array', items: new OA\Items(type: 'integer')),
            ],
        )),
        responses: [
            new OA\Response(response: 200, description: 'Marked as read.', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function markManyRead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $this->notificationService->markManyAsRead($validated['ids'], $request->user());

        return response()->json(['message' => __('messages.notifications_marked_read')]);
    }
}
