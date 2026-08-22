<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Api\ClientPasswordResetRequest;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Password Reset', description: 'Server-to-server: registered OAuth clients trigger reset emails for their users.')]
class PasswordResetController extends BaseController
{
    public function __construct(
        protected PasswordResetService $passwordResetService,
    ) {}

    /**
     * Allow a registered OAuth client to request a password reset email be
     * sent to one of its users, authenticating via client_id/client_secret.
     */
    #[OA\Post(
        path: '/api/password/forgot',
        summary: 'Request a password reset email (client-authenticated, no user token)',
        tags: ['Password Reset'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['client_id', 'client_secret'],
            properties: [
                new OA\Property(property: 'client_id', type: 'string'),
                new OA\Property(property: 'client_secret', type: 'string'),
                new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true, description: 'Required if membership_code is omitted'),
                new OA\Property(property: 'membership_code', type: 'string', nullable: true, description: 'Required if email is omitted'),
            ],
        )),
        responses: [
            new OA\Response(response: 200, description: 'Email sent (if the account exists).', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 401, description: 'Invalid client credentials'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function forgot(ClientPasswordResetRequest $request): JsonResponse
    {
        $this->passwordResetService->sendResetLinkForClient(
            $request->string('client_id')->toString(),
            $request->string('client_secret')->toString(),
            $request->filled('email') ? $request->string('email')->toString() : null,
            $request->filled('membership_code') ? $request->string('membership_code')->toString() : null,
        );

        return response()->json(['message' => __('messages.password_reset_email_sent')]);
    }
}
