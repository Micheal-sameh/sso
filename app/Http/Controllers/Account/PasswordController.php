<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\BaseController;
use App\Http\Requests\UpdateOwnPasswordRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PasswordController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected UserService $userService,
    ) {}

    public function update(UpdateOwnPasswordRequest $request): RedirectResponse
    {
        $this->userService->updateOwnPassword(Auth::user(), $request->string('password')->toString());

        return $this->redirectRoute('account.show', 'Password updated.', 'success');
    }
}
