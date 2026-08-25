<?php

namespace App\Repositories\Eloquent;

use App\Models\SpiritualNote;
use App\Models\User;
use App\Repositories\Contracts\SpiritualNoteRepositoryContract;
use Illuminate\Support\Collection;

class SpiritualNoteRepository implements SpiritualNoteRepositoryContract
{
    public function forUserAndDate(User $user, string $date): Collection
    {
        return SpiritualNote::with('label')
            ->where('user_id', $user->id)
            ->where('date', $date)
            ->get();
    }

    public function forUserBetween(User $user, string $from, string $to): Collection
    {
        return SpiritualNote::with('label')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$from, $to])
            ->orderByDesc('date')
            ->get();
    }

    public function upsert(User $user, int $labelId, string $date, bool $isChecked, ?string $note): void
    {
        SpiritualNote::updateOrCreate(
            ['user_id' => $user->id, 'spiritual_note_label_id' => $labelId, 'date' => $date],
            ['is_checked' => $isChecked, 'note' => $note],
        );
    }

    public function userIdsWithCheckedEntryOnDate(string $date): Collection
    {
        return SpiritualNote::where('date', $date)
            ->where('is_checked', true)
            ->distinct()
            ->pluck('user_id');
    }
}
