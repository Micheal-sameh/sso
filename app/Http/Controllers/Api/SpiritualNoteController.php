<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\SpiritualNoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'SpiritualNotes', description: 'Daily spiritual-note checklist: a shared set of labels each user checks off and annotates every day.')]
class SpiritualNoteController extends BaseController
{
    public function __construct(
        protected SpiritualNoteService $spiritualNoteService,
    ) {}

    #[OA\Get(
        path: '/api/spiritual-notes/today',
        summary: "Today's spiritual-note labels, with the current user's saved checkbox/note state",
        security: [['bearerAuth' => []]],
        tags: ['SpiritualNotes'],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'entries', type: 'array', items: new OA\Items(properties: [
                        new OA\Property(property: 'label_id', type: 'integer'),
                        new OA\Property(property: 'label_name', type: 'string'),
                        new OA\Property(property: 'is_checked', type: 'boolean'),
                        new OA\Property(property: 'note', type: 'string', nullable: true),
                    ])),
                ],
            )),
        ],
    )]
    public function today(Request $request): JsonResponse
    {
        return response()->json([
            'entries' => $this->spiritualNoteService->today($request->user()),
        ]);
    }

    #[OA\Post(
        path: '/api/spiritual-notes/today',
        summary: "Save the current user's checkbox/note state for today",
        security: [['bearerAuth' => []]],
        tags: ['SpiritualNotes'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['entries'],
            properties: [
                new OA\Property(property: 'entries', type: 'array', items: new OA\Items(properties: [
                    new OA\Property(property: 'label_id', type: 'integer'),
                    new OA\Property(property: 'is_checked', type: 'boolean'),
                    new OA\Property(property: 'note', type: 'string', nullable: true),
                ])),
            ],
        )),
        responses: [
            new OA\Response(response: 200, description: 'Saved.', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function saveToday(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entries' => ['required', 'array'],
            'entries.*.label_id' => ['required', 'integer'],
            'entries.*.is_checked' => ['sometimes', 'boolean'],
            'entries.*.note' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->spiritualNoteService->saveToday($request->user(), $validated['entries']);

        return response()->json(['message' => __('messages.spiritual_note_saved')]);
    }

    #[OA\Get(
        path: '/api/spiritual-notes/history',
        summary: "The current user's past daily entries, grouped by date",
        security: [['bearerAuth' => []]],
        tags: ['SpiritualNotes'],
        parameters: [
            new OA\Parameter(name: 'from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
        ],
    )]
    public function history(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date'],
        ]);

        return response()->json([
            'days' => $this->spiritualNoteService->history($request->user(), $validated['from'] ?? null, $validated['to'] ?? null),
        ]);
    }
}
