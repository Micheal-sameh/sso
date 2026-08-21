<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\CreateStageDTO;
use App\Http\Controllers\BaseController;
use App\Http\Requests\CreateStageRequest;
use App\Http\Requests\UpdateStageRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Models\Stage;
use App\Services\StageService;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class StageController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected StageService $stageService,
    ) {}

    public function index(): Response
    {
        return $this->inertia('Admin/Stages/Index', [
            'stages' => $this->stageService->listAll(),
        ]);
    }

    public function store(CreateStageRequest $request): RedirectResponse
    {
        $this->stageService->create(CreateStageDTO::fromRequest($request));

        return $this->redirectRoute('admin.stages.index', 'Stage created successfully.');
    }

    public function update(Stage $stage, UpdateStageRequest $request): RedirectResponse
    {
        $this->stageService->update($stage, CreateStageDTO::fromRequest($request));

        return $this->redirectRoute('admin.stages.index', 'Stage updated successfully.');
    }

    public function destroy(Stage $stage): RedirectResponse
    {
        $this->stageService->delete($stage);

        return $this->redirectBack('Stage deleted.', 'success');
    }
}
