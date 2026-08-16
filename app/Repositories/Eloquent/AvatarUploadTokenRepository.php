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

        return AvatarUploadToken::create([
            'user_id' => $user->id,
            'token' => Str::random(64),
            'expires_at' => now()->addMinutes(3),
        ]);
    }

    public function findValid(string $token): ?AvatarUploadToken
    {
        return AvatarUploadToken::with('user')
            ->where('token', $token)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function markUsed(AvatarUploadToken $token): void
    {
        $token->update(['used_at' => now()]);
    }
}
