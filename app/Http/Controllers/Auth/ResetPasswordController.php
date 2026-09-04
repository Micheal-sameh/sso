<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Auth\PasswordResetException;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Repositories\Contracts\PasswordResetLinkTokenRepositoryContract;
use App\Services\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ResetPasswordController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected PasswordResetService $passwordResetService,
        protected PasswordResetLinkTokenRepositoryContract $passwordResetLinkTokenRepository,
    ) {}

    public function show(string $token, Request $request): Response
    {
        return $this->inertia('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function reset(ResetPasswordRequest $request): RedirectResponse
    {
        $this->passwordResetService->reset(
            $request->string('token')->toString(),
            $request->string('email')->toString(),
            $request->string('password')->toString(),
        );

        return $this->redirectRoute('login', __('messages.password_reset_success'), 'success');
    }

    public function showLink(string $token): Response
    {
        if ($this->passwordResetLinkTokenRepository->findValid($token) === null) {
            return $this->inertia('Auth/ResetPasswordLink', ['status' => 'invalid']);
        }

        return $this->inertia('Auth/ResetPasswordLink', ['status' => 'form', 'token' => $token]);
    }

    public function resetLink(string $token, Request $request): Response
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $this->passwordResetService->resetViaLink($token, $validated['password']);
        } catch (PasswordResetException) {
            return $this->inertia('Auth/ResetPasswordLink', ['status' => 'invalid']);
        }

        return $this->inertia('Auth/ResetPasswordLink', ['status' => 'success']);
    }
}
