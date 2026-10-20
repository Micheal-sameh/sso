<?php

namespace App\Repositories\Eloquent;

use App\Models\AvatarUploadToken;
use App\Models\User;
use App\Repositories\Contracts\AvatarUploadTokenRepositoryContract;
use Illuminate\Support\Str;

class AvatarUploadTokenRepository implements AvatarUploadTokenRepositoryContract
{
    public function create(User $user): AvatarUploadToken
    {
        AvatarUploadToken::where('user_id', $user->id)->delete();

        $plain = Str::random(64);

        // Only a hash is stored, so a leaked database cannot be replayed as
        // live links; the plaintext lives solely on the returned instance
        // (never saved) for building the link handed to the user.
        $record = AvatarUploadToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $plain),
            'expires_at' => now()->addMinutes(3),
        ]);
        $record->setAttribute('token', $plain);
        $record->syncOriginal();

        return $record;
    }

    public function findValid(string $token): ?AvatarUploadToken
    {
        return AvatarUploadToken::with('user')
            ->where('token', hash('sha256', $token))
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function markUsed(AvatarUploadToken $token): void
    {
        $token->update(['used_at' => now()]);
    }
}
