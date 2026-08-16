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
     * Update a user's admin-editable details (name, email, phone, stage, role, avatar),
     * keeping the Spatie role assignment in sync with the chosen role. The membership
     * code is permanently immutable once a user is created, even for a super admin.
     *
     * @throws SelfModificationException
     */
    public function updateDetails(User $user, array $validated): User
    {
        $newRole = UserRole::from($validated['role']);

        if (Auth::id() === $user->id && $newRole !== $user->role) {
            throw new SelfModificationException('You cannot change your own role.');
        }

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'stage_id' => $validated['stage_id'],
            'role' => $newRole,
        ];

        if (isset($validated['avatar']) && $validated['avatar'] instanceof UploadedFile) {
            $data['avatar'] = $this->storeAvatar($user, $validated['avatar']);
        }

        $user = $this->userRepository->update($user, $data);
        $user->syncRoles($newRole === UserRole::User ? [] : [$newRole->value]);

        return $user->fresh(['mfa', 'stage', 'roles']);
    }

    /**
     * Store an uploaded avatar under a stable, user-specific filename.
     */
    private function storeAvatar(User $user, UploadedFile $avatar): string
    {
        $extension = $avatar->getClientOriginalExtension();

        return $avatar->storeAs('avatars', "{$user->uuid}.{$extension}", 'public');
    }

    /**
     * Change the authenticated user's own password after current-password verification
     * (enforced by UpdateOwnPasswordRequest's `current_password` rule).
     */
    public function updateOwnPassword(User $user, string $password): void
    {
        $user->forceFill(['password' => $password])->save();
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
