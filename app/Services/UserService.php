<?php

namespace App\Services;

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
}
