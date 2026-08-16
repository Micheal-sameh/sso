<?php

namespace App\Http\Controllers;

use App\Http\Traits\HandlesInertiaResponses;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class AvatarUploadController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected UserService $userService,
    ) {}

    public function show(Request $request, User $user): Response
    {
        return $this->inertia('AvatarUpload', [
            'userName' => $user->name,
            'avatar' => $user->avatar_url,
            'submitUrl' => $request->fullUrl(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'avatar' => ['required', 'image', 'max:2048'],
        ]);

        $this->userService->updateAvatar($user, $validated['avatar']);

        return $this->redirectBack('Profile picture updated. Thank you!', 'success');
    }
}
