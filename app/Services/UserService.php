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
     * Create a new user from the given DTO.
     */
    public function create(CreateUserDTO $dto): User
    {
        return $this->userRepository->create($dto);
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
     * Paginate users, optionally filtered by search term and role.
     */
    public function paginate(int $perPage, ?string $search, ?UserRole $role): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage, $search, $role);
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
     * Grant or revoke a user's SSO admin access via the super admin role.
     *
     * @throws SelfModificationException
     */
    public function toggleAdminAccess(User $user): User
    {
        if (Auth::id() === $user->id) {
            throw new SelfModificationException;
        }

        return $this->userRepository->toggleAdminAccess($user, AdminRole::SuperAdmin->value);
    }
}
