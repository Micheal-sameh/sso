<?php

namespace App\Services;

use App\Repositories\ProjectBlackListRepository;

class ProjectBlackListService
{
    public function __construct(protected ProjectBlackListRepository $projectBlackListRepository) {}

    public function getAllProjectBlackLists($filters = [])
    {
        return $this->projectBlackListRepository->getAllProjectBlackLists($filters);
    }

    public function getProjectBlackListById($id)
    {
        return $this->projectBlackListRepository->getProjectBlackListById($id);
    }

    public function createProjectBlackList(array $data)
    {
        return $this->projectBlackListRepository->createProjectBlackList($data);
    }

    public function updateProjectBlackList($projectBlackList, array $data)
    {
        return $this->projectBlackListRepository->updateProjectBlackList($projectBlackList, $data);
    }

    public function deleteProjectBlackList($projectBlackList)
    {
        return $this->projectBlackListRepository->deleteProjectBlackList($projectBlackList);
    }
}
