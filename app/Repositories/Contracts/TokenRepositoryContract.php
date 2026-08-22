<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TokenRepositoryContract
{
    public function listAllPaginated(int $perPage = 20): LengthAwarePaginator;

    public function listForUser(int $userId): Collection;

    public function revoke(string $tokenId): void;

    /**
     * Revoke a token only if it belongs to the given user. Returns false if
     * the token doesn't exist or belongs to someone else.
     */
    public function revokeForUser(string $tokenId, int $userId): bool;

    /**
     * Revoke all active tokens (and their refresh tokens) issued to a user for a given client.
     */
    public function revokeAllForUserAndClient(int $userId, string $clientId): void;
}
