<?php

namespace App\Repositories\Eloquent;

use App\DTOs\CreateUserDTO;
use App\Enums\AdminRole;
use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class UserRepository implements UserRepositoryContract
{
    /**
     * Find a user by email, eager loading their MFA settings.
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->with(['mfa', 'roles', 'stage'])->first();
    }

    /**
     * Find a user by membership code, eager loading their MFA settings.
     */
    public function findByMembershipCode(string $membershipCode): ?User
    {
        return User::where('membership_code', $membershipCode)->with(['mfa', 'roles', 'stage'])->first();
    }

    /**
     * Find a user by UUID or fail, eager loading their MFA settings.
     */
    public function findByUuid(string $uuid): User
    {
        return User::where('uuid', $uuid)->with(['mfa', 'roles', 'stage'])->firstOrFail();
    }

    /**
     * Find a user by primary key or fail, eager loading their MFA settings.
     */
    public function findById(int $id): User
    {
        return User::with(['mfa', 'roles', 'stage'])->findOrFail($id);
    }

    /**
     * Paginate users, optionally filtered by search term, role, and stage.
     */
    public function paginate(int $perPage = 20, ?string $search = null, ?UserRole $role = null, ?int $stageId = null): LengthAwarePaginator
    {
        return User::query()
            ->with(['roles', 'stage'])
            ->when($search, fn ($query) => $query->where(
                fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('membership_code', 'like', "%{$search}%")
            ))
            ->when($role, fn ($query) => $query->where('role', $role))
            ->when($stageId, fn ($query) => $query->where('stage_id', $stageId))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Paginate users who currently hold an admin panel role (Admin or Super Admin),
     * optionally filtered by search term.
     */
    public function paginateAdmins(int $perPage = 20, ?string $search = null): LengthAwarePaginator
    {
        return User::query()
            ->with(['roles', 'stage'])
            ->whereHas('roles', fn ($query) => $query->whereIn('name', [
                AdminRole::SuperAdmin->value,
                AdminRole::Admin->value,
            ]))
            ->when($search, fn ($query) => $query->where(
                fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('membership_code', 'like', "%{$search}%")
            ))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Create a new user from the given DTO.
     */
    public function create(CreateUserDTO $dto): User
    {
        return User::create([
            'uuid' => Str::uuid()->toString(),
            'membership_code' => $dto->membershipCode,
            'name' => $dto->name,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'date_of_birth' => $dto->dateOfBirth,
            'password' => $dto->password,
            'role' => $dto->role,
            'stage_id' => $dto->stageId,
        ]);
    }

    /**
     * Update a user with the given attributes.
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh(['mfa', 'stage']);
    }

    /**
     * Toggle a user's active status.
     */
    public function toggleActive(User $user): User
    {
        $user->update(['is_active' => ! $user->is_active]);

        return $user->fresh();
    }

    /**
     * Toggle a user's SSO admin access by granting or revoking the super admin role.
     */
    public function toggleAdminAccess(User $user, string $roleName): User
    {
        if ($user->hasRole($roleName)) {
            $user->removeRole($roleName);
        } else {
            $user->assignRole($roleName);
        }

        return $user->fresh(['mfa', 'roles', 'stage']);
    }

    /**
     * Add a user to a group without disturbing their other group memberships.
     */
    public function attachGroup(User $user, int $groupId): void
    {
        $user->groups()->syncWithoutDetaching([$groupId]);
    }

    /**
     * Remove a user from a group.
     */
    public function detachGroup(User $user, int $groupId): void
    {
        $user->groups()->detach($groupId);
    }

    /**
     * List every user's identifying fields only, for lightweight admin pickers.
     */
    public function allLite(): Collection
    {
        return User::query()
            ->with('stage')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'membership_code', 'stage_id']);
    }

    /**
     * Active users whose date of birth falls on today's month and day.
     */
    public function withBirthdayToday(): Collection
    {
        return User::query()
            ->active()
            ->whereNotNull('date_of_birth')
            ->whereMonth('date_of_birth', now()->month)
            ->whereDay('date_of_birth', now()->day)
            ->get();
    }
}
