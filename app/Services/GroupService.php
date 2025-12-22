<?php

namespace App\Services;

use App\Repositories\GroupRepository;

class GroupService
{
    public function __construct(protected GroupRepository $groupRepository) {}

    public function getAllGroups($filters = [])
    {
        return $this->groupRepository->getAllGroups($filters);
    }

    public function getGroupById($id)
    {
        return $this->groupRepository->getGroupById($id);
    }

    public function createGroup(array $data)
    {
        return $this->groupRepository->createGroup($data);
    }

    public function updateGroup($group, array $data)
    {
        return $this->groupRepository->updateGroup($group, $data);
    }

    public function deleteGroup($group)
    {
        return $this->groupRepository->deleteGroup($group);
    }
}
