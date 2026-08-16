<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Api\ClientPasswordResetRequest;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends BaseController
{
    public function __construct(
        protected PasswordResetService $passwordResetService,
    ) {}

    /**
     * Allow a registered OAuth client to request a password reset email be
     * sent to one of its users, authenticating via client_id/client_secret.
     */
    public function forgot(ClientPasswordResetRequest $request): JsonResponse
    {
        $this->passwordResetService->sendResetLinkForClient(
            $request->string('client_id')->toString(),
            $request->string('client_secret')->toString(),
            $request->filled('email') ? $request->string('email')->toString() : null,
            $request->filled('membership_code') ? $request->string('membership_code')->toString() : null,
        );

        return response()->json(['message' => 'Password reset email sent.']);
    }
}
