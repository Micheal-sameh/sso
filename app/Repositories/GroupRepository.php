<?php

namespace App\Repositories;

use App\Models\Group;

class GroupRepository
{
    public function __construct(protected Group $model) {}

    public function getAllGroups($filters = [])
    {
        $query = $this->model->query()->select('id', 'name', 'abbreviation', 'created_at');

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('abbreviation', 'like', "%{$search}%");
        }

        return $query->paginate(10);
    }

    public function getGroupById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function createGroup(array $data)
    {
        return $this->model->create($data);
    }

    public function updateGroup($group, array $data)
    {
        $group->update($data);

        return $group;
    }

    public function deleteGroup($group)
    {
        return $group->delete();
    }
}
