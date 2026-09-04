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

        return PasswordResetLinkToken::create([
            'user_id' => $user->id,
            'token' => Str::random(64),
            'expires_at' => now()->addMinutes(5),
        ]);
    }

    public function findValid(string $token): ?PasswordResetLinkToken
    {
        return PasswordResetLinkToken::with('user')
            ->where('token', $token)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function markUsed(PasswordResetLinkToken $token): void
    {
        $token->update(['used_at' => now()]);
    }
}
