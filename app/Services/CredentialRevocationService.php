<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\TokenRepositoryContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Invalidates everything a user could still be authenticated with once their
 * password changes: OAuth/personal access tokens (and refresh tokens),
 * "remember me" cookies, and other browser sessions. Without this a stolen
 * token or session outlives the password reset meant to cut it off.
 */
class CredentialRevocationService
{
    public function __construct(
        protected TokenRepositoryContract $tokenRepository,
    ) {}

    /**
     * @param  string|null  $keepSessionId  the caller's current session, to keep them signed in
     * @param  string|null  $keepTokenId  the caller's current API token, likewise
     */
    public function revokeAll(User $user, ?string $keepSessionId = null, ?string $keepTokenId = null): void
    {
        $this->tokenRepository->revokeAllForUser($user->id, $keepTokenId);

        $user->forceFill(['remember_token' => Str::random(60)])->save();

        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->when($keepSessionId !== null, fn ($q) => $q->where('id', '!=', $keepSessionId))
                ->delete();
        }
    }
}
