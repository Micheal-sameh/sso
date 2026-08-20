<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\NotificationDTO;
use App\Http\Controllers\BaseController;
use App\Http\Requests\NotificationRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Services\GroupService;
use App\Services\NotificationService;
use App\Services\StageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class NotificationController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected NotificationService $notificationService,
        protected StageService $stageService,
        protected GroupService $groupService,
        protected UserRepositoryContract $userRepository,
    ) {}

    public function index(): Response
    {
        return $this->inertia('Admin/Notifications/Index', [
            'notifications' => $this->notificationService->listAllPaginated(20),
            'stages' => $this->stageService->listAll(),
            'groups' => $this->groupService->listAll(),
            'users' => $this->userRepository->allLite(),
        ]);
    }

    public function store(NotificationRequest $request): RedirectResponse
    {
        $dto = NotificationDTO::fromRequest($request);

        SendNotificationJob::dispatch(
            message: $dto->message,
            targetType: $dto->targetType,
            stageIds: $dto->stageIds,
            groupIds: $dto->groupIds,
            userIds: $dto->userIds,
            senderId: Auth::id(),
            imagePath: $dto->image instanceof UploadedFile ? $dto->image->store('notification-uploads') : null,
        );

        return $this->redirectRoute('admin.notifications.index', 'Notification is being sent.');
    }

    public function destroy(Notification $notification): RedirectResponse
    {
        $this->notificationService->delete($notification);

        return $this->redirectBack('Notification deleted.', 'success');
    }
}
