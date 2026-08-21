<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\CmcPost;
use App\Services\CmcPostService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'CMC Posts')]
class CmcPostController extends BaseController
{
    public function __construct(
        protected CmcPostService $cmcPostService,
    ) {}

    #[OA\Get(
        path: '/api/cmc-posts',
        summary: 'List CMC posts (paginated, newest first)',
        security: [['bearerAuth' => []]],
        tags: ['CMC Posts'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                new OA\Schema(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/CmcPost')),
                ]),
            ])),
        ],
    )]
    public function index(): JsonResponse
    {
        return response()->json(
            $this->cmcPostService->listAllPaginated(20)
                ->through(fn (CmcPost $post) => $this->cmcPostService->toArray($post))
        );
    }

    #[OA\Get(
        path: '/api/cmc-posts/{cmcPost}',
        summary: 'Get a single CMC post',
        security: [['bearerAuth' => []]],
        tags: ['CMC Posts'],
        parameters: [
            new OA\Parameter(name: 'cmcPost', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(ref: '#/components/schemas/CmcPost')),
            new OA\Response(response: 404, description: 'Not found'),
        ],
    )]
    public function show(CmcPost $cmcPost): JsonResponse
    {
        $cmcPost->loadMissing('creator');

        return response()->json($this->cmcPostService->toArray($cmcPost));
    }
}
