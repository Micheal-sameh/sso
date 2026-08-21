<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\CmcPost;
use App\Services\CmcPostService;
use App\Services\CopticCalendarService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Home', description: 'Dashboard: today\'s Coptic date/synaxarium and the latest CMC posts.')]
class HomeController extends BaseController
{
    public function __construct(
        protected CmcPostService $cmcPostService,
        protected CopticCalendarService $copticCalendarService,
    ) {}

    #[OA\Get(
        path: '/api/home',
        summary: 'Dashboard data (Coptic date + recent CMC posts)',
        security: [['bearerAuth' => []]],
        tags: ['Home'],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'coptic', ref: '#/components/schemas/CopticDate'),
                new OA\Property(property: 'recent_posts', type: 'array', items: new OA\Items(ref: '#/components/schemas/CmcPost')),
            ])),
        ],
    )]
    public function index(): JsonResponse
    {
        return response()->json([
            'coptic' => $this->copticCalendarService->today(),
            'recent_posts' => $this->cmcPostService->listRecent(5)
                ->map(fn (CmcPost $post) => $this->cmcPostService->toArray($post))
                ->values(),
        ]);
    }
}
