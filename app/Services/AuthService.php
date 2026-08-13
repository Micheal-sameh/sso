<?php

namespace App\Services;

use App\DTOs\LoginDTO;
use App\Exceptions\Auth\AuthenticationException;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        protected UserRepositoryContract $userRepository,
    ) {}

    /**
     * Attempt to authenticate a user with the given credentials.
     *
     * @throws AuthenticationException
     */
    public function attempt(LoginDTO $dto): User
    {
        $user = $this->userRepository->findByMembershipCode($dto->membershipCode);

        if ($user === null) {
            throw new AuthenticationException('Invalid credentials.');
        }

        if (! $user->is_active) {
            throw new AuthenticationException('Account is inactive.');
        }

        if (! Hash::check($dto->password, $user->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        Auth::login($user, $dto->remember);

        return $user;
    }

    /**
     * Determine whether the given user must complete MFA verification.
     */
    public function requiresMfa(User $user): bool
    {
        return $user->mfa?->is_enabled ?? false;
    }

    /**
     * Log the current user out and invalidate their session.
     */
    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }
}
