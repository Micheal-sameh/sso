<?php

namespace App\Http\Controllers\Account;

use App\Exceptions\Auth\InvalidMfaCodeException;
use App\Http\Controllers\BaseController;
use App\Http\Requests\UpdateOwnPasswordRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Services\MfaService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PasswordController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected UserService $userService,
        protected MfaService $mfaService,
    ) {}

    /**
     * @throws InvalidMfaCodeException
     */
    public function update(UpdateOwnPasswordRequest $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->mfa?->is_enabled && ! $this->mfaService->verify($user, $request->string('code')->toString())) {
            throw new InvalidMfaCodeException;
        }

        $this->userService->updateOwnPassword($user, $request->string('password')->toString());

        return $this->redirectRoute('account.show', __('messages.password_updated'), 'success');
    }
}
