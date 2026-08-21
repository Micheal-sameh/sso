<?php

namespace App\Http\Controllers\Api;

use App\DTOs\UserDTO;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Profile')]
class ProfileController extends BaseController
{
    #[OA\Get(
        path: '/api/me',
        summary: 'Get the authenticated user\'s profile',
        security: [['bearerAuth' => []]],
        tags: ['Profile'],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function me(Request $request): JsonResponse
    {
        return response()->json(UserDTO::fromModel($request->user())->toArray());
    }
}
