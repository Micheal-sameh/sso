<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Repositories\UserRepository;

class UserService
{
    public function __construct(protected UserRepository $userRepository) {}

    public function getAllUsers($filters = [])
    {
        $users = $this->userRepository->getAllUsers($filters);

        $totalUsers = $this->userRepository->countUsers();
        $activeUsers = $this->userRepository->countUsers(UserStatus::ACTIVE);
        $inactiveUsers = $this->userRepository->countUsers(UserStatus::INACTIVE);

        return compact('users', 'totalUsers', 'activeUsers', 'inactiveUsers');
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
}
