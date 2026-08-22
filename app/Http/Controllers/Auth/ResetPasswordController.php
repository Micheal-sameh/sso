<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Services\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ResetPasswordController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected PasswordResetService $passwordResetService,
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
}
