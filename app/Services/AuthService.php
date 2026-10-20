<?php

namespace App\Services;

use App\DTOs\LoginDTO;
use App\Exceptions\Auth\AuthenticationException;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        protected UserRepositoryContract $userRepository,
        protected LoginAttemptLimiter $loginAttemptLimiter,
        protected TrustedDeviceService $trustedDeviceService,
    ) {}

    /**
     * Attempt to authenticate a user with the given credentials, establishing
     * a session (web login) unless MFA is still owed — in that case the
     * session is deliberately left unauthenticated until MfaController::verify()
     * confirms the second factor, so protected routes stay inaccessible in
     * the meantime (rather than relying on route-level checks to catch it).
     *
     * @throws AuthenticationException
     */
    public function attempt(LoginDTO $dto, ?string $deviceToken = null): User
    {
        $user = $this->verifyCredentials($dto->membershipCode, $dto->password, $deviceToken);

        if (! $this->requiresMfa($user)) {
            Auth::login($user, $dto->remember);
        }

        return $user;
    }

    /**
     * Verify membership code + password without establishing a session,
     * for stateless (API/mobile) callers that issue their own tokens.
     *
     * @throws AuthenticationException
     */
    public function verifyCredentials(string $membershipCode, string $password, ?string $deviceToken = null): User
    {
        $user = $this->userRepository->findByMembershipCode($membershipCode);

        // A device already trusted for this account is counted separately from
        // strangers guessing the code, so they can't lock the real user out.
        $deviceId = $user !== null ? $this->trustedDeviceService->deviceId($deviceToken, $user) : null;
        $bucket = $deviceId !== null ? 'device:'.$deviceId : LoginAttemptLimiter::UNKNOWN_BUCKET;

        $this->loginAttemptLimiter->ensureNotLocked($membershipCode, $bucket);

        // Unknown codes are counted too, so lockouts don't reveal which exist.
        if ($user === null) {
            $this->loginAttemptLimiter->recordFailure($membershipCode, $bucket);

            throw new AuthenticationException(__('messages.invalid_credentials'));
        }

        if (! $user->is_active) {
            throw new AuthenticationException(__('messages.account_inactive'));
        }

        if (! Hash::check($password, $user->password)) {
            $this->loginAttemptLimiter->recordFailure($membershipCode, $bucket);

            throw new AuthenticationException(__('messages.invalid_credentials'));
        }

        $this->loginAttemptLimiter->clear($membershipCode, $bucket);

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
     * Issue a short-lived, self-contained token identifying a user who has
     * passed step 1 (credentials) of a stateless login and still needs to
     * complete MFA. Avoids needing server-side session state for API callers.
     */
    public function issueMfaChallenge(User $user): string
    {
        return Crypt::encryptString(json_encode([
            'user_id' => $user->id,
            'expires' => now()->addMinutes(5)->timestamp,
        ]));
    }

    /**
     * Resolve the user identified by a still-valid MFA challenge token.
     *
     * @throws AuthenticationException
     */
    public function resolveMfaChallenge(string $token): User
    {
        try {
            $payload = json_decode(Crypt::decryptString($token), true);
        } catch (DecryptException) {
            throw new AuthenticationException(__('messages.invalid_or_expired_verification_session'));
        }

        if (! is_array($payload) || ($payload['expires'] ?? 0) < now()->timestamp) {
            throw new AuthenticationException(__('messages.invalid_or_expired_verification_session'));
        }

        try {
            return $this->userRepository->findById((int) ($payload['user_id'] ?? 0));
        } catch (ModelNotFoundException) {
            throw new AuthenticationException(__('messages.invalid_or_expired_verification_session'));
        }
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
