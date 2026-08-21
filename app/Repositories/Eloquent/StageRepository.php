<?php

namespace App\Repositories\Eloquent;

use App\Models\Stage;
use App\Repositories\Contracts\StageRepositoryContract;
use Illuminate\Support\Collection;

class StageRepository implements StageRepositoryContract
{
    /**
     * List all stages ordered for display, with a count of users in each.
     */
    public function all(): Collection
    {
        return Stage::withCount('users')->orderBy('sort_order')->orderBy('name')->get();
    }

    /**
     * Create a new stage, appending it after the current highest sort order.
     */
    public function create(array $data): Stage
    {
        $nextSortOrder = (int) Stage::max('sort_order') + 1;

        return Stage::create([...$data, 'sort_order' => $nextSortOrder]);
    }

    /**
     * Update a stage's name and code.
     */
    public function update(Stage $stage, array $data): Stage
    {
        $stage->update($data);

        return $stage->fresh();
    }

    /**
     * Delete a stage.
     */
    public function delete(Stage $stage): bool
    {
        return (bool) $stage->delete();
    }
}
