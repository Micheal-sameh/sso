<?php

namespace App\Http\Controllers;

use App\Services\YoutubeLiveStatusService;
use Illuminate\Http\JsonResponse;

class YoutubeLiveStatusController extends BaseController
{
    public function __construct(
        protected YoutubeLiveStatusService $youtubeLiveStatusService,
    ) {}

    public function show(): JsonResponse
    {
        return response()->json([
            'youtubeLive' => $this->youtubeLiveStatusService->current(),
        ]);
    }
}
