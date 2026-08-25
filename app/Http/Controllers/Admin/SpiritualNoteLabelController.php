<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Requests\SpiritualNoteLabelRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Models\SpiritualNoteLabel;
use App\Services\SpiritualNoteService;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class SpiritualNoteLabelController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected SpiritualNoteService $spiritualNoteService,
    ) {}

    public function index(): Response
    {
        return $this->inertia('Admin/SpiritualNoteLabels/Index', [
            'labels' => $this->spiritualNoteService->listLabels(),
        ]);
    }

    public function store(SpiritualNoteLabelRequest $request): RedirectResponse
    {
        $this->spiritualNoteService->createLabel($request->validated());

        return $this->redirectRoute('admin.spiritual-note-labels.index', 'Label created successfully.');
    }

    public function update(SpiritualNoteLabel $spiritualNoteLabel, SpiritualNoteLabelRequest $request): RedirectResponse
    {
        $this->spiritualNoteService->updateLabel($spiritualNoteLabel, $request->validated());

        return $this->redirectRoute('admin.spiritual-note-labels.index', 'Label updated successfully.');
    }

    public function destroy(SpiritualNoteLabel $spiritualNoteLabel): RedirectResponse
    {
        $this->spiritualNoteService->deleteLabel($spiritualNoteLabel);

        return $this->redirectBack('Label deleted.', 'success');
    }
}
