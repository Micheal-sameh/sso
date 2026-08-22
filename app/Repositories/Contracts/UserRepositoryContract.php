<?php

namespace App\Repositories\Contracts;

use App\DTOs\CreateUserDTO;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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

    public function toggleRequiresDataUpdate(User $user): User;

    public function toggleAdminAccess(User $user, string $roleName): User;

    public function attachGroup(User $user, int $groupId): void;

    public function detachGroup(User $user, int $groupId): void;

    public function allLite(): Collection;

    /**
     * Active users whose date of birth falls on today's month and day.
     */
    public function withBirthdayToday(): Collection;

    /**
     * Active users opted into donation reminders whose reminder day is today
     * (clamped to the current month's length).
     */
    public function wantingMonthlyDonationReminderToday(): Collection;

    /**
     * Every active user currently opted into donation reminders, regardless of day.
     */
    public function wantingDonationReminders(): Collection;
}
