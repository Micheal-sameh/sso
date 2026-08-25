<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;

interface SpiritualNoteRepositoryContract
{
    public function forUserAndDate(User $user, string $date): Collection;

    public function forUserBetween(User $user, string $from, string $to): Collection;

    public function upsert(User $user, int $labelId, string $date, bool $isChecked, ?string $note): void;

    /**
     * IDs of users who have at least one checked entry on the given date.
     */
    public function userIdsWithCheckedEntryOnDate(string $date): Collection;
}
