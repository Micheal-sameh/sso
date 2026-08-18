<?php

namespace App\Services;

use App\DTOs\GroupDTO;
use App\Models\Group;
use App\Repositories\Contracts\GroupRepositoryContract;
use Illuminate\Support\Collection;

class GroupService
{
    public function __construct(
        protected GroupRepositoryContract $groupRepository,
    ) {}

    /**
     * List all groups ordered for display, with member counts.
     */
    public function listAll(): Collection
    {
        return $this->groupRepository->all();
    }

    /**
     * Create a new group from the given DTO.
     */
    public function create(GroupDTO $dto): Group
    {
        return $this->groupRepository->create([
            'name' => $dto->name,
            'description' => $dto->description,
        ]);
    }

    /**
     * Update an existing group from the given DTO.
     */
    public function update(Group $group, GroupDTO $dto): Group
    {
        return $this->groupRepository->update($group, [
            'name' => $dto->name,
            'description' => $dto->description,
        ]);
    }

    /**
     * Delete a group.
     */
    public function delete(Group $group): void
    {
        $this->groupRepository->delete($group);
    }
}
