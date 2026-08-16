<?php

namespace App\Http\Controllers;

use App\Http\Traits\HandlesInertiaResponses;
use App\Repositories\Contracts\AvatarUploadTokenRepositoryContract;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Response;

class AvatarUploadController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected UserService $userService,
        protected AvatarUploadTokenRepositoryContract $avatarUploadTokenRepository,
    ) {}

    public function show(string $token): Response
    {
        $record = $this->avatarUploadTokenRepository->findValid($token);

        if ($record === null) {
            return $this->inertia('AvatarUpload', ['status' => 'invalid']);
        }

        return $this->inertia('AvatarUpload', [
            'status' => 'form',
            'userName' => $record->user->name,
            'avatar' => $record->user->avatar_url,
            'token' => $token,
        ]);
    }

    public function update(string $token, Request $request): Response
    {
        $record = $this->avatarUploadTokenRepository->findValid($token);

        if ($record === null) {
            return $this->inertia('AvatarUpload', ['status' => 'invalid']);
        }

        $validated = $request->validate([
            'avatar' => ['required', 'image', 'max:2048'],
        ]);

        $this->userService->updateAvatar($record->user, $validated['avatar']);
        $this->avatarUploadTokenRepository->markUsed($record);

        return $this->inertia('AvatarUpload', [
            'status' => 'success',
            'userName' => $record->user->name,
        ]);
    }
}
