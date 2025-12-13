<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function __construct(protected User $model) {}

    public function getAllUsers(array $filters = [])
    {
        $query = $this->model->query();

        // Apply status filter
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        // Apply search filter
        if (isset($filters['search']) && $filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('membership_code', 'like', "%{$search}%");
            });
        }

        return $query->paginate(10);
    }

    public function getUserById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function updateUser(User $user, array $data)
    {
        $user->update($data);

        return $user;
    }

    public function bulkUpdateStatus(array $userIds, bool $status)
    {
        return $this->model->whereIn('id', $userIds)->update(['status' => $status]);
    }

    public function countUsers($status = null)
    {
        $query = $this->model->query();

        if (! is_null($status)) {
            $query->where('status', $status);
        }

        return $query->count();
    }
}
