<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\YoutubeVideo;
use App\Services\YoutubeVideoService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Videos', description: 'The synced YouTube channel video library.')]
class VideoController extends BaseController
{
    public function __construct(
        protected YoutubeVideoService $youtubeVideoService,
    ) {}

    #[OA\Get(
        path: '/api/videos',
        summary: 'List all synced YouTube videos, newest first',
        security: [['bearerAuth' => []]],
        tags: ['Videos'],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'videos', type: 'array', items: new OA\Items(ref: '#/components/schemas/YoutubeVideo')),
            ])),
        ],
    )]
    public function index(): JsonResponse
    {
        return response()->json([
            'videos' => $this->youtubeVideoService->listAllPaginated(20)
                ->through(fn (YoutubeVideo $video) => $this->youtubeVideoService->toArray($video)),
        ]);
    }
}
