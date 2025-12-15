<?php

namespace App\Repositories;

use App\Enums\UserStatus;
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

    public function getFamilyMembers($membershipCode, $excludeUserId = null)
    {
        // Extract the family code pattern (E1C1Fxxxx) from membership code
        // Format: E1C1FxxxxNRx where same family has same E1C1Fxxxx but different NR
        $familyCode = '';

        // Extract the family code (everything before NR)
        if (preg_match('/^(E\d+C\d+F\d+)(NR\d+)$/', strtoupper($membershipCode), $matches)) {
            $familyCode = $matches[1];
        }

        if (! $familyCode) {
            return collect();
        }

        $query = $this->model->where('membership_code', 'like', $familyCode.'%');

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        return $query->get();
    }

    public function adminUsers()
    {
        return $this->model->whereHas('ssoUser');
    }

    /**
     * Return aggregated user counts to avoid multiple queries.
     *
     * @return array{total:int,active:int,inactive:int}
     */
    public function getUserCounts()
    {
        $row = $this->model->selectRaw(
            "COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as inactive",
            [UserStatus::ACTIVE, UserStatus::INACTIVE]
        )->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'active' => (int) ($row->active ?? 0),
            'inactive' => (int) ($row->inactive ?? 0),
        ];
    }
}
