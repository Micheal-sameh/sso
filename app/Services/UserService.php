<?php

namespace App\Services;

use App\Events\UserGroupsChanged;
use App\Repositories\UserRepository;

class UserService
{
    public function __construct(protected UserRepository $userRepository) {}

    public function getAllUsers($filters = [])
    {
        $users = $this->userRepository->getAllUsers($filters);
        // Get aggregated counts with a single query where possible
        $counts = $this->userRepository->getUserCounts();
        $totalUsers = $counts['total'];
        $activeUsers = $counts['active'];
        $inactiveUsers = $counts['inactive'];

        // adminUsers() now returns a query builder so count() runs in DB
        $adminUsers = $this->userRepository->adminUsers()->count();

        return compact('users', 'totalUsers', 'activeUsers', 'inactiveUsers', 'adminUsers');
    }

    public function getUserById($id)
    {
        return $this->userRepository->getUserById($id);
    }

    public function updateUser($user, array $data)
    {
        return $this->userRepository->updateUser($user, $data);
    }

    public function bulkUpdateStatus(array $userIds, bool $status)
    {
        return $this->userRepository->bulkUpdateStatus($userIds, $status);
    }

    public function getFamilyMembers($membershipCode, $excludeUserId = null)
    {
        return $this->userRepository->getFamilyMembers($membershipCode, $excludeUserId);
    }

    public function addGroup($user, $groupId)
    {
        if ($user->groups()->where('group_id', $groupId)->exists()) {
            return false;
        }

        $user->groups()->attach($groupId);

        return true;
    }

    public function removeGroup($user, $groupId)
    {
        return (bool) $user->groups()->detach($groupId);
    }

    public function syncGroups($user, array $groupIds = [])
    {
        $user->groups()->sync($groupIds);
        event(new UserGroupsChanged($groupIds, $user));

        return $user;
    }
}
