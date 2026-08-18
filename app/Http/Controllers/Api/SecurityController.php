<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Auth\InvalidMfaCodeException;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Api\MfaCodeRequest;
use App\Http\Requests\Api\MfaConfirmRequest;
use App\Http\Requests\Api\UpdatePasswordRequest;
use App\Services\MfaService;
use App\Services\UserService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class SecurityController extends BaseController
{
    public function __construct(
        protected MfaService $mfaService,
        protected UserService $userService,
    ) {}

    /**
     * Current MFA status. Recovery codes are only ever returned at the moment
     * they're (re)generated (enable/confirm), never re-fetchable here.
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'mfa_enabled' => $request->user()->mfa?->is_enabled ?? false,
        ]);
    }

    /**
     * Start MFA setup: generate a secret + QR code. Unlike the web flow
     * (which stashes the pending secret in the session), a stateless caller
     * gets it back as an encrypted `setup_token` to round-trip to confirm().
     */
    public function mfaEnable(Request $request): JsonResponse
    {
        $secret = $this->mfaService->generateSecret();

        return response()->json([
            'setup_token' => Crypt::encryptString($secret),
            'secret' => $secret,
            'qr_code_svg' => $this->mfaService->getQrCodeSvg($request->user(), $secret),
        ]);
    }

    /**
     * @throws InvalidMfaCodeException
     */
    public function mfaConfirm(MfaConfirmRequest $request): JsonResponse
    {
        try {
            $secret = Crypt::decryptString($request->string('setup_token')->toString());
        } catch (DecryptException) {
            throw new InvalidMfaCodeException('This setup session has expired. Start MFA setup again.');
        }

        $this->mfaService->enableMfa($request->user(), $secret, $request->string('code')->toString());

        return response()->json([
            'message' => '2FA enabled successfully.',
            'recovery_codes' => $request->user()->fresh()->mfa->recovery_codes,
        ]);
    }

    /**
     * @throws InvalidMfaCodeException
     */
    public function mfaDisable(MfaCodeRequest $request): JsonResponse
    {
        $this->mfaService->disableMfa($request->user(), $request->string('code')->toString());

        return response()->json(['message' => '2FA has been disabled.']);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $this->userService->updateOwnPassword($request->user(), $request->string('password')->toString());

        return response()->json(['message' => 'Password updated.']);
    }
}
