<?php

namespace App\Services;

use App\Exceptions\Auth\OAuthClientException;
use App\Exceptions\Auth\PasswordResetException;
use App\Exceptions\Auth\UserNotFoundException;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Laravel\Passport\ClientRepository;

class PasswordResetService
{
    public function __construct(
        protected UserRepositoryContract $userRepository,
        protected ClientRepository $clientRepository,
    ) {}

    /**
     * Send a password reset email to the given address.
     *
     * @return string The Password broker status constant (e.g. Password::RESET_LINK_SENT).
     */
    public function sendResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    /**
     * Complete a password reset using a token issued via sendResetLink().
     *
     * @throws PasswordResetException
     */
    public function reset(string $token, string $email, string $password): void
    {
        $status = Password::reset(
            ['token' => $token, 'email' => $email, 'password' => $password, 'password_confirmation' => $password],
            function (User $user, string $password): void {
                $user->forceFill(['password' => $password])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new PasswordResetException(__($status));
        }
    }

    /**
     * Issue a reset token for the given user and build the reset URL, without
     * emailing it, for the admin to relay via another channel (e.g. WhatsApp).
     */
    public function generateResetLink(User $user): string
    {
        $token = Password::broker()->createToken($user);

        return route('password.reset', ['token' => $token]).'?email='.urlencode($user->email);
    }

    /**
     * Send a reset link on behalf of a registered OAuth client, authenticating
     * the client via its id/secret rather than a user session or bearer token.
     *
     * @throws OAuthClientException
     * @throws UserNotFoundException
     */
    public function sendResetLinkForClient(string $clientId, string $clientSecret, ?string $email, ?string $membershipCode): void
    {
        $client = $this->clientRepository->find($clientId);

        if ($client === null || $client->revoked || ! Hash::check($clientSecret, $client->secret)) {
            throw new OAuthClientException('Invalid client credentials.');
        }

        $user = $email !== null
            ? $this->userRepository->findByEmail($email)
            : $this->userRepository->findByMembershipCode($membershipCode);

        if ($user === null) {
            throw new UserNotFoundException;
        }

        $this->sendResetLink($user->email);
    }
}
