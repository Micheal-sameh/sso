<?php

namespace App\Repositories\Contracts;

use App\Models\PasswordResetLinkToken;
use App\Models\User;

interface PasswordResetLinkTokenRepositoryContract
{
    /**
     * Issue a fresh one-time reset token for the user, invalidating any
     * previously issued tokens so only the newest link stays valid.
     */
    public function create(User $user): PasswordResetLinkToken;

    /**
     * Find a token that is unused and not yet expired, eager loading its user.
     */
    public function findValid(string $token): ?PasswordResetLinkToken;

    /**
     * Mark a token as consumed so it cannot be used again.
     */
    public function markUsed(PasswordResetLinkToken $token): void;
}
