<?php

namespace App\Repositories\Eloquent;

use App\DTOs\CreateUserDTO;
use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class UserRepository implements UserRepositoryContract
{
    /**
     * Find a user by email, eager loading their MFA settings.
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->with(['mfa', 'roles'])->first();
    }

    /**
     * Find a user by membership code, eager loading their MFA settings.
     */
    public function findByMembershipCode(string $membershipCode): ?User
    {
        return User::where('membership_code', $membershipCode)->with(['mfa', 'roles'])->first();
    }

    /**
     * Find a user by UUID or fail, eager loading their MFA settings.
     */
    public function findByUuid(string $uuid): User
    {
        return User::where('uuid', $uuid)->with(['mfa', 'roles'])->firstOrFail();
    }

    /**
     * Find a user by primary key or fail, eager loading their MFA settings.
     */
    public function findById(int $id): User
    {
        return User::with(['mfa', 'roles'])->findOrFail($id);
    }

    /**
     * Paginate users, optionally filtered by search term and role.
     */
    public function paginate(int $perPage = 20, ?string $search = null, ?UserRole $role = null): LengthAwarePaginator
    {
        return User::query()
            ->with(['roles'])
            ->when($search, fn ($query) => $query->where(
                fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('membership_code', 'like', "%{$search}%")
            ))
            ->when($role, fn ($query) => $query->where('role', $role))
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
            'password' => $dto->password,
            'role' => $dto->role,
        ]);
    }

    /**
     * Update a user with the given attributes.
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh(['mfa']);
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

        return $user->fresh(['mfa', 'roles']);
    }
}
