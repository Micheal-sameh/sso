<?php

namespace App\Repositories\Eloquent;

use App\Models\Group;
use App\Repositories\Contracts\GroupRepositoryContract;
use Illuminate\Support\Collection;

class GroupRepository implements GroupRepositoryContract
{
    /**
     * List all groups ordered by name, with a count of member users.
     */
    public function all(): Collection
    {
        return Group::withCount('users')->orderBy('name')->get();
    }

    /**
     * Create a new group.
     */
    public function create(array $data): Group
    {
        return Group::create($data);
    }

    /**
     * Update an existing group.
     */
    public function update(Group $group, array $data): Group
    {
        $group->update($data);

        return $group->fresh();
    }

    /**
     * Delete a group. Membership and client access rows for it cascade-delete
     * automatically since group membership is optional, unlike Stage.
     */
    public function delete(Group $group): bool
    {
        return (bool) $group->delete();
    }
}
