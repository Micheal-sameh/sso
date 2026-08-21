<?php

namespace App\Services;

use App\DTOs\CreateStageDTO;
use App\Exceptions\Auth\StageInUseException;
use App\Models\Stage;
use App\Repositories\Contracts\StageRepositoryContract;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class StageService
{
    public function __construct(
        protected StageRepositoryContract $stageRepository,
    ) {}

    /**
     * List all stages ordered for display, with user counts.
     */
    public function listAll(): Collection
    {
        return $this->stageRepository->all();
    }

    /**
     * Create a new stage from the given DTO.
     */
    public function create(CreateStageDTO $dto): Stage
    {
        return $this->stageRepository->create([
            'name' => $dto->name,
            'code' => Str::lower($dto->code),
        ]);
    }

    /**
     * Update a stage's name and code.
     */
    public function update(Stage $stage, CreateStageDTO $dto): Stage
    {
        return $this->stageRepository->update($stage, [
            'name' => $dto->name,
            'code' => Str::lower($dto->code),
        ]);
    }

    /**
     * Delete a stage, refusing if any user is still assigned to it.
     *
     * @throws StageInUseException
     */
    public function delete(Stage $stage): void
    {
        try {
            $this->stageRepository->delete($stage);
        } catch (QueryException $e) {
            throw new StageInUseException;
        }
    }
}
