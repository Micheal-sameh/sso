<?php

namespace App\Services;

use App\DTOs\CreateUserDTO;
use App\Enums\AdminPermission;
use App\Enums\AdminRole;
use App\Enums\UserRole;
use App\Exceptions\Auth\SelfModificationException;
use App\Exceptions\Auth\UnauthorizedRoleChangeException;
use App\Models\User;
use App\Repositories\Contracts\AvatarUploadTokenRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class UserService
{
    public function __construct(
        protected UserRepositoryContract $userRepository,
        protected AvatarUploadTokenRepositoryContract $avatarUploadTokenRepository,
        protected OAuthClientService $oauthClientService,
    ) {}

    /**
     * Create a new user from the given DTO, granting the matching admin
     * role automatically when created as Admin or Super Admin.
     */
    public function create(CreateUserDTO $dto): User
    {
        if ($dto->role !== UserRole::User && ! $this->canManageAdminAccess()) {
            throw new UnauthorizedRoleChangeException;
        }

        $user = $this->userRepository->create($dto);

        if ($dto->role !== UserRole::User) {
            $user->assignRole($dto->role->value);
        }

        return $user;
    }

    /**
     * Update a user's admin-editable details (name, email, phone, date of birth, stage, role, avatar),
     * keeping the Spatie role assignment in sync with the chosen role. The membership
     * code is permanently immutable once a user is created, even for a super admin.
     *
     * @throws SelfModificationException
     */
    public function updateDetails(User $user, array $validated): User
    {
        $newRole = UserRole::from($validated['role']);

        if (Auth::id() === $user->id && $newRole !== $user->role) {
            throw new SelfModificationException(__('messages.cannot_change_own_role'));
        }

        $touchesAdminRole = $newRole !== UserRole::User || $user->role !== UserRole::User;

        if ($newRole !== $user->role && $touchesAdminRole && ! $this->canManageAdminAccess()) {
            throw new UnauthorizedRoleChangeException;
        }

        $user = $this->userRepository->update($user, [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'stage_id' => $validated['stage_id'],
            'role' => $newRole,
        ]);

        if (isset($validated['avatar']) && $validated['avatar'] instanceof UploadedFile) {
            $this->updateAvatar($user, $validated['avatar']);
        }

        $user->syncRoles($newRole === UserRole::User ? [] : [$newRole->value]);

        $this->oauthClientService->notifyClientsUserUpdated($user);

        return $user->fresh(['mfa', 'stage', 'roles']);
    }

    /**
     * Replace a user's profile picture, used both by the admin edit form and
     * the tokenized self-service avatar upload link.
     */
    public function updateAvatar(User $user, UploadedFile $avatar): void
    {
        $user->addMedia($avatar)->toMediaCollection('avatar');
    }

    /**
     * Remove a user's profile picture, if one is set.
     */
    public function removeAvatar(User $user): void
    {
        $user->clearMediaCollection('avatar');
    }

    /**
     * Issue a one-time, 3-minute upload link a user can visit, without logging
     * in, to upload their own profile picture. Intended for an admin to copy
     * and relay via another channel (e.g. WhatsApp). Generating a new link
     * invalidates any link issued to the same user previously.
     */
    public function generateAvatarUploadLink(User $user): string
    {
        $token = $this->avatarUploadTokenRepository->create($user);

        return route('avatar-upload.show', ['token' => $token->token]);
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
     * Active users whose date of birth falls on the given month/day, regardless of year.
     */
    public function birthdaysOnDate(int $month, int $day): Collection
    {
        return $this->userRepository->withBirthdayOnDate($month, $day);
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
     * Toggle whether a user must update their data before they can authenticate
     * into any app other than the SSO admin panel itself.
     */
    public function toggleRequiresDataUpdate(User $user): User
    {
        return $this->userRepository->toggleRequiresDataUpdate($user);
    }

    /**
     * Add a user to a group.
     */
    public function addToGroup(User $user, int $groupId): void
    {
        $this->userRepository->attachGroup($user, $groupId);
    }

    /**
     * Remove a user from a group.
     */
    public function removeFromGroup(User $user, int $groupId): void
    {
        $this->userRepository->detachGroup($user, $groupId);
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

    /**
     * Whether the authenticated user holds the permission that gates assigning
     * or changing the Admin and Super Admin roles.
     */
    protected function canManageAdminAccess(): bool
    {
        return (bool) Auth::user()?->can(AdminPermission::ManageAdminAccess->value);
    }
}
