<?php

namespace App\Services;

use App\DTOs\CreateUserDTO;
use App\Enums\AdminRole;
use App\Enums\UserRole;
use App\Exceptions\Auth\SelfModificationException;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class UserService
{
    public function __construct(
        protected UserRepositoryContract $userRepository,
    ) {}

    /**
     * Create a new user from the given DTO, granting the matching admin
     * role automatically when created as Admin or Super Admin.
     */
    public function create(CreateUserDTO $dto): User
    {
        $user = $this->userRepository->create($dto);

        if ($dto->role === UserRole::Admin || $dto->role === UserRole::SuperAdmin) {
            $user->assignRole($dto->role->value);
        }

        return $user;
    }

    /**
     * Update a user's profile with validated attributes, handling avatar upload.
     */
    public function update(User $user, array $validated): User
    {
        $data = collect($validated)->except('avatar')->all();

        if (isset($validated['avatar']) && $validated['avatar'] instanceof UploadedFile) {
            $extension = $validated['avatar']->getClientOriginalExtension();
            $path = $validated['avatar']->storeAs('avatars', "{$user->uuid}.{$extension}", 'public');
            $data['avatar'] = $path;
        }

        return $this->userRepository->update($user, $data);
    }

    /**
     * Update a user's admin-editable details (name, email, membership code, stage, role),
     * keeping the Spatie role assignment in sync with the chosen role.
     *
     * @throws SelfModificationException
     */
    public function updateDetails(User $user, array $validated): User
    {
        $newRole = UserRole::from($validated['role']);

        if (Auth::id() === $user->id && $newRole !== $user->role) {
            throw new SelfModificationException('You cannot change your own role.');
        }

        $user = $this->userRepository->update($user, [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'membership_code' => $validated['membership_code'],
            'stage_id' => $validated['stage_id'],
            'role' => $newRole,
        ]);

        $user->syncRoles($newRole === UserRole::User ? [] : [$newRole->value]);

        return $user->fresh(['mfa', 'stage', 'roles']);
    }

    /**
     * Paginate users, optionally filtered by search term, role, and stage.
     */
    public function paginate(int $perPage, ?string $search, ?UserRole $role, ?int $stageId = null): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage, $search, $role, $stageId);
    }

    /**
     * Paginate users who currently hold an admin panel role, optionally filtered by search term.
     */
    public function paginateAdmins(int $perPage, ?string $search): LengthAwarePaginator
    {
        return $this->userRepository->paginateAdmins($perPage, $search);
    }

    /**
     * Toggle a user's active status.
     */
    public function toggleActive(User $user): User
    {
        return $this->userRepository->toggleActive($user);
    }

    /**
     * Resolve the authenticated user from the given request.
     */
    public function getFromToken(Request $request): User
    {
        return $request->user();
    }

    /**
     * Grant or revoke a user's SSO admin panel access via the admin role.
     * This never grants full Super Admin privileges.
     *
     * @throws SelfModificationException
     */
    public function toggleAdminAccess(User $user): User
    {
        if (Auth::id() === $user->id) {
            throw new SelfModificationException;
        }

        return $this->userRepository->toggleAdminAccess($user, AdminRole::Admin->value);
    }
}
