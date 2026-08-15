<?php

namespace App\Repositories\Contracts;

use App\DTOs\CreateUserDTO;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryContract
{
    public function findByEmail(string $email): ?User;

    public function findByMembershipCode(string $membershipCode): ?User;

    public function findByUuid(string $uuid): User;

    public function findById(int $id): User;

    public function paginate(int $perPage = 20, ?string $search = null, ?UserRole $role = null, ?int $stageId = null): LengthAwarePaginator;

    public function paginateAdmins(int $perPage = 20, ?string $search = null): LengthAwarePaginator;

    public function create(CreateUserDTO $dto): User;

    public function update(User $user, array $data): User;

    public function toggleActive(User $user): User;

    public function toggleAdminAccess(User $user, string $roleName): User;
}
