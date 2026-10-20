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
use App\Services\TrustedDeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Auth', description: 'First-party mobile login (membership code + password), no OAuth consent screen.')]
class AuthController extends BaseController
{
    public function __construct(
        protected AuthService $authService,
        protected MfaService $mfaService,
        protected TrustedDeviceService $trustedDeviceService,
    ) {}

    /**
     * First-party mobile login: verify membership code + password directly
     * against this SSO (no OAuth consent screen, since this isn't a
     * third-party client). Issues a token immediately, or an MFA challenge
     * if the account has two-factor enabled.
     */
    #[OA\Post(
        path: '/api/auth/login',
        summary: 'Log in with membership code + password',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['membership_code', 'password'],
            properties: [
                new OA\Property(property: 'membership_code', type: 'string', example: 'AV-00123'),
                new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
            ],
        )),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Either an access token, or an MFA challenge if the account has 2FA enabled.',
                content: new OA\JsonContent(oneOf: [
                    new OA\Schema(properties: [
                        new OA\Property(property: 'token', type: 'string'),
                        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                    ]),
                    new OA\Schema(properties: [
                        new OA\Property(property: 'mfa_required', type: 'boolean', example: true),
                        new OA\Property(property: 'mfa_token', type: 'string'),
                    ]),
                ]),
            ),
            new OA\Response(response: 401, description: 'Invalid credentials', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->verifyCredentials(
            $request->string('membership_code')->toString(),
            $request->string('password')->toString(),
            $request->header(TrustedDeviceService::HEADER),
        );

        if ($this->authService->requiresMfa($user)) {
            return response()->json([
                'mfa_required' => true,
                'mfa_token' => $this->authService->issueMfaChallenge($user),
            ]);
        }

        return $this->tokenResponse($user, $request);
    }

    /**
     * Complete a login that required MFA, exchanging the challenge token
     * from login() plus a TOTP/recovery code for an access token.
     *
     * @throws InvalidMfaCodeException
     */
    #[OA\Post(
        path: '/api/auth/mfa/verify',
        summary: 'Complete a login that required MFA',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['mfa_token', 'code'],
            properties: [
                new OA\Property(property: 'mfa_token', type: 'string', description: 'Returned by /api/auth/login when mfa_required is true'),
                new OA\Property(property: 'code', type: 'string', description: 'TOTP code or a recovery code'),
            ],
        )),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Access token issued.',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'token', type: 'string'),
                    new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                    new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                ]),
            ),
            new OA\Response(response: 422, description: 'Invalid or expired MFA code/token', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function verifyMfa(MfaLoginVerifyRequest $request): JsonResponse
    {
        $user = $this->authService->resolveMfaChallenge($request->string('mfa_token')->toString());

        $deviceToken = $request->header(TrustedDeviceService::HEADER);
        $this->authService->ensureMfaAllowed($user, $deviceToken);

        if (! $this->mfaService->verify($user, $request->string('code')->toString())) {
            $this->authService->recordMfaFailure($user, $deviceToken);

            throw new InvalidMfaCodeException;
        }

        $this->authService->recordMfaSuccess($user, $deviceToken);

        return $this->tokenResponse($user, $request);
    }

    /**
     * Revoke the access token used to authenticate this request.
     */
    #[OA\Post(
        path: '/api/auth/logout',
        summary: 'Revoke the current access token',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Logged out.', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->token()?->revoke();

        return response()->json(['message' => __('messages.logged_out')]);
    }

    private function tokenResponse(User $user, Request $request): JsonResponse
    {
        $token = $user->createToken('mobile')->accessToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            // Store securely and send back as the X-Device-Token header on
            // later logins so wrong guesses from elsewhere can't lock this device out.
            'device_token' => $this->trustedDeviceService->issue($user, $request->header(TrustedDeviceService::HEADER)),
            'user' => UserDTO::fromModel($user)->toArray(),
        ]);
    }
}
