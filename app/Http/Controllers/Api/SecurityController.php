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
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Security', description: 'MFA (TOTP) setup and password changes.')]
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
    #[OA\Get(
        path: '/api/security',
        summary: 'Current MFA status',
        security: [['bearerAuth' => []]],
        tags: ['Security'],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'mfa_enabled', type: 'boolean'),
            ])),
        ],
    )]
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
    #[OA\Post(
        path: '/api/security/mfa/enable',
        summary: 'Start MFA setup (generate secret + QR code)',
        security: [['bearerAuth' => []]],
        tags: ['Security'],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'setup_token', type: 'string', description: 'Encrypted secret; round-trip this to /api/security/mfa/confirm'),
                new OA\Property(property: 'secret', type: 'string'),
                new OA\Property(property: 'qr_code_svg', type: 'string'),
            ])),
        ],
    )]
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
    #[OA\Post(
        path: '/api/security/mfa/confirm',
        summary: 'Confirm MFA setup with a TOTP code',
        security: [['bearerAuth' => []]],
        tags: ['Security'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['setup_token', 'code'],
            properties: [
                new OA\Property(property: 'setup_token', type: 'string'),
                new OA\Property(property: 'code', type: 'string'),
            ],
        )),
        responses: [
            new OA\Response(response: 200, description: 'MFA enabled.', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'recovery_codes', type: 'array', items: new OA\Items(type: 'string')),
            ])),
            new OA\Response(response: 422, description: 'Invalid code or expired setup session', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function mfaConfirm(MfaConfirmRequest $request): JsonResponse
    {
        try {
            $secret = Crypt::decryptString($request->string('setup_token')->toString());
        } catch (DecryptException) {
            throw new InvalidMfaCodeException(__('messages.mfa_setup_session_expired'));
        }

        $this->mfaService->enableMfa($request->user(), $secret, $request->string('code')->toString());

        return response()->json([
            'message' => __('messages.mfa_enabled'),
            'recovery_codes' => $request->user()->fresh()->mfa->recovery_codes,
        ]);
    }

    /**
     * @throws InvalidMfaCodeException
     */
    #[OA\Post(
        path: '/api/security/mfa/disable',
        summary: 'Disable MFA',
        security: [['bearerAuth' => []]],
        tags: ['Security'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['code'],
            properties: [new OA\Property(property: 'code', type: 'string', description: 'TOTP code or a recovery code')],
        )),
        responses: [
            new OA\Response(response: 200, description: 'MFA disabled.', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 422, description: 'Invalid code', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function mfaDisable(MfaCodeRequest $request): JsonResponse
    {
        $this->mfaService->disableMfa($request->user(), $request->string('code')->toString());

        return response()->json(['message' => __('messages.mfa_disabled')]);
    }

    /**
     * @throws InvalidMfaCodeException
     */
    #[OA\Post(
        path: '/api/security/password',
        summary: 'Change password (requires MFA code)',
        security: [['bearerAuth' => []]],
        tags: ['Security'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['current_password', 'password', 'password_confirmation', 'code'],
            properties: [
                new OA\Property(property: 'current_password', type: 'string', format: 'password'),
                new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
                new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                new OA\Property(property: 'code', type: 'string', description: 'TOTP code or a recovery code'),
            ],
        )),
        responses: [
            new OA\Response(response: 200, description: 'Password updated.', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 422, description: 'Validation error or invalid MFA code', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        if (! $this->mfaService->verify($request->user(), $request->string('code')->toString())) {
            throw new InvalidMfaCodeException;
        }

        $this->userService->updateOwnPassword($request->user(), $request->string('password')->toString());

        return response()->json(['message' => __('messages.password_updated')]);
    }
}
