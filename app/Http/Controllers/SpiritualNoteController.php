<?php

namespace App\Http\Controllers;

use App\Http\Traits\HandlesInertiaResponses;
use App\Services\SpiritualNoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class SpiritualNoteController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected SpiritualNoteService $spiritualNoteService,
    ) {}

    public function show(Request $request): Response
    {
        $today = $this->spiritualNoteService->currentDate();
        $date = $request->query('date');
        $date = is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && $date <= $today ? $date : $today;

        return $this->inertia('SpiritualNotes/Show', [
            'entries' => $this->spiritualNoteService->forDate($request->user(), $date),
            'date' => $date,
            'today' => $today,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'entries' => ['required', 'array'],
            'entries.*.label_id' => ['required', 'integer'],
            'entries.*.is_checked' => ['sometimes', 'boolean'],
            'entries.*.note' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->spiritualNoteService->saveToday($request->user(), $validated['entries']);

        return $this->redirectBack('Saved.', 'success');
    }
}
