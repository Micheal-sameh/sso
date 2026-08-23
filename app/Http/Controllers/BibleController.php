<?php

namespace App\Http\Controllers;

use App\Http\Traits\HandlesInertiaResponses;
use App\Services\BibleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class BibleController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected BibleService $bibleService,
    ) {}

    public function index(): Response
    {
        return $this->inertia('Liturgy/Bible/Index', [
            'books' => $this->bibleService->books(),
        ]);
    }

    public function chapter(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'book' => ['required', 'integer', 'min:1'],
            'chapter' => ['required', 'integer', 'min:1'],
        ]);

        return response()->json([
            'verses' => $this->bibleService->chapter((int) $validated['book'], (int) $validated['chapter']),
        ]);
    }
}
