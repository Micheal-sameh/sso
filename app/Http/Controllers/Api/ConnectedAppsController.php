<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Token;
use App\Repositories\Contracts\TokenRepositoryContract;
use App\Services\OAuthClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Connected Apps', description: 'Third-party OAuth clients the user has authorized.')]
class ConnectedAppsController extends BaseController
{
    public function __construct(
        protected TokenRepositoryContract $tokenRepository,
        protected OAuthClientService $oauthClientService,
    ) {}

    #[OA\Get(
        path: '/api/connected-apps',
        summary: 'List authorized OAuth clients (active tokens)',
        security: [['bearerAuth' => []]],
        tags: ['Connected Apps'],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(type: 'array', items: new OA\Items(type: 'object'))),
        ],
    )]
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->tokenRepository->listForUser($request->user()->id));
    }

    #[OA\Delete(
        path: '/api/connected-apps/{tokenId}',
        summary: 'Revoke an authorized app\'s access',
        security: [['bearerAuth' => []]],
        tags: ['Connected Apps'],
        parameters: [
            new OA\Parameter(name: 'tokenId', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Revoked.', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Token not found or not owned by this user'),
        ],
    )]
    public function revoke(Request $request, string $tokenId): JsonResponse
    {
        $token = Token::where('id', $tokenId)->where('user_id', $request->user()->id)->first();

        $revoked = $this->tokenRepository->revokeForUser($tokenId, $request->user()->id);

        abort_if(! $revoked, 404);

        if ($token !== null) {
            $this->oauthClientService->notifyClientLogout($token->client_id, $token->user_id);
        }

        return response()->json(['message' => __('messages.app_access_revoked')]);
    }
}
