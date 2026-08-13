<?php

namespace App\Http\Controllers\Account;

use App\DTOs\UserDTO;
use App\Http\Controllers\BaseController;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class ProfileController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected UserService $userService,
    ) {}

    public function show(): Response
    {
        return $this->inertia('Account/Show', ['user' => UserDTO::fromModel(Auth::user())->toArray()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->userService->update(Auth::user(), $request->validated());

        return $this->redirectRoute('account.show', 'Profile updated.');
    }
}
