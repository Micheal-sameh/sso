<?php

namespace App\Repositories\Eloquent;

use App\Models\Token;
use App\Repositories\Contracts\TokenRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Laravel\Passport\RefreshToken;

class TokenRepository implements TokenRepositoryContract
{
    /**
     * Paginate all issued tokens with their user and client relations.
     */
    public function listAllPaginated(int $perPage = 20): LengthAwarePaginator
    {
        return Token::with(['user', 'client'])->latest()->paginate($perPage)->withQueryString();
    }

    /**
     * List all active (non-revoked) tokens belonging to a user.
     */
    public function listForUser(int $userId): Collection
    {
        return Token::with(['client'])->where('user_id', $userId)->where('revoked', false)->get();
    }

    /**
     * Revoke a token and its associated refresh token.
     */
    public function revoke(string $tokenId): void
    {
        $token = Token::findOrFail($tokenId);
        $token->revoked = true;
        $token->save();

        RefreshToken::where('access_token_id', $tokenId)->update(['revoked' => true]);
    }

    /**
     * Revoke a token only if it belongs to the given user.
     */
    public function revokeForUser(string $tokenId, int $userId): bool
    {
        $token = Token::where('id', $tokenId)->where('user_id', $userId)->first();

        if ($token === null) {
            return false;
        }

        $token->revoked = true;
        $token->save();

        RefreshToken::where('access_token_id', $tokenId)->update(['revoked' => true]);

        return true;
    }
}
