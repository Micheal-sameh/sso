<?php

namespace App\Repositories;

use App\Models\ProjectBlackList;

class ProjectBlackListRepository
{
    public function __construct(protected ProjectBlackList $model) {}

    public function getAllProjectBlackLists($filters = [])
    {
        $query = $this->model->query();

        if (isset($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('membership_code', 'like', "%{$search}%");
            });
        }

        return $query->with(['project', 'user', 'creator'])->paginate(10);
    }

    public function getProjectBlackListById($id)
    {
        return $this->model->with(['project', 'user', 'creator'])->findOrFail($id);
    }

    public function createProjectBlackList(array $data)
    {
        return $this->model->create($data);
    }

    public function updateProjectBlackList($projectBlackList, array $data)
    {
        $projectBlackList->update($data);

        return $projectBlackList;
    }

    public function deleteProjectBlackList($projectBlackList)
    {
        return $projectBlackList->delete();
    }

    public function bulkDeleteByIds(array $ids)
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function findByUserProject($userId, $projectId)
    {
        $record = $this->model->where('user_id', $userId)->where('project_id', $projectId)->first();

        return $record ? false : true;
    }
}
