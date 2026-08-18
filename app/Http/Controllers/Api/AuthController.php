<?php

namespace App\Http\Controllers\Api;

use App\DTOs\UserDTO;
use App\Exceptions\Auth\InvalidMfaCodeException;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\MfaLoginVerifyRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Services\MfaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends BaseController
{
    public function __construct(
        protected AuthService $authService,
        protected MfaService $mfaService,
    ) {}

    /**
     * First-party mobile login: verify membership code + password directly
     * against this SSO (no OAuth consent screen, since this isn't a
     * third-party client). Issues a token immediately, or an MFA challenge
     * if the account has two-factor enabled.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->verifyCredentials(
            $request->string('membership_code')->toString(),
            $request->string('password')->toString(),
        );

        if ($this->authService->requiresMfa($user)) {
            return response()->json([
                'mfa_required' => true,
                'mfa_token' => $this->authService->issueMfaChallenge($user),
            ]);
        }

        return $this->tokenResponse($user);
    }

    /**
     * Complete a login that required MFA, exchanging the challenge token
     * from login() plus a TOTP/recovery code for an access token.
     *
     * @throws InvalidMfaCodeException
     */
    public function verifyMfa(MfaLoginVerifyRequest $request): JsonResponse
    {
        $user = $this->authService->resolveMfaChallenge($request->string('mfa_token')->toString());

        if (! $this->mfaService->verify($user, $request->string('code')->toString())) {
            throw new InvalidMfaCodeException;
        }

        return $this->tokenResponse($user);
    }

    /**
     * Revoke the access token used to authenticate this request.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->token()?->revoke();

        return response()->json(['message' => 'Logged out.']);
    }

    private function tokenResponse(User $user): JsonResponse
    {
        $token = $user->createToken('mobile')->accessToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => UserDTO::fromModel($user)->toArray(),
        ]);
    }
}
