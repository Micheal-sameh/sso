<?php

namespace App\Repositories\Eloquent;

use App\Models\PasswordResetLinkToken;
use App\Models\User;
use App\Repositories\Contracts\PasswordResetLinkTokenRepositoryContract;
use Illuminate\Support\Str;

class PasswordResetLinkTokenRepository implements PasswordResetLinkTokenRepositoryContract
{
    public function create(User $user): PasswordResetLinkToken
    {
        PasswordResetLinkToken::where('user_id', $user->id)->delete();

        $plain = Str::random(64);

        // Only a hash is stored, so a leaked database cannot be replayed as
        // live links; the plaintext lives solely on the returned instance
        // (never saved) for building the link handed to the user.
        $record = PasswordResetLinkToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $plain),
            'expires_at' => now()->addMinutes(5),
        ]);
        $record->setAttribute('token', $plain);
        $record->syncOriginal();

        return $record;
    }

    public function findValid(string $token): ?PasswordResetLinkToken
    {
        return PasswordResetLinkToken::with('user')
            ->where('token', hash('sha256', $token))
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function markUsed(PasswordResetLinkToken $token): void
    {
        $token->update(['used_at' => now()]);
    }
}
