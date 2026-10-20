<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Services\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Inertia\Response;

class ForgotPasswordController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected PasswordResetService $passwordResetService,
    ) {}

    public function show(): Response
    {
        return $this->inertia('Auth/ForgotPassword');
    }

    public function send(ForgotPasswordRequest $request): RedirectResponse
    {
        // Same answer whether or not the address exists (or is throttled), so
        // this form can't be used to discover which emails have accounts.
        $this->passwordResetService->sendResetLink($request->string('email')->toString());

        return $this->redirectRoute('password.request', __('messages.password_reset_if_exists'));
    }
}
