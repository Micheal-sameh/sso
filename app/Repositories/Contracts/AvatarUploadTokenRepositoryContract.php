<?php

namespace App\Repositories\Contracts;

use App\Models\AvatarUploadToken;
use App\Models\User;

interface AvatarUploadTokenRepositoryContract
{
    /**
     * Issue a fresh one-time upload token for the user, invalidating any
     * previously issued tokens so only the newest link stays valid.
     */
    public function create(User $user): AvatarUploadToken;

    /**
     * Find a token that is unused and not yet expired, eager loading its user.
     */
    public function findValid(string $token): ?AvatarUploadToken;

    /**
     * Mark a token as consumed so it cannot be used again.
     */
    public function markUsed(AvatarUploadToken $token): void;
}
