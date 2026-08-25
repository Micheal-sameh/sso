<?php

namespace App\Services;

use App\DTOs\NotificationDTO;
use App\Enums\NotificationTargetType;
use App\Enums\NotificationType;
use App\Models\SpiritualNoteLabel;
use App\Models\User;
use App\Repositories\Contracts\SpiritualNoteLabelRepositoryContract;
use App\Repositories\Contracts\SpiritualNoteRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SpiritualNoteService
{
    /**
     * History requests without an explicit range are capped to this many
     * days back, so a user can't force an unbounded table scan.
     */
    private const DEFAULT_HISTORY_DAYS = 60;

    public function __construct(
        protected SpiritualNoteLabelRepositoryContract $labelRepository,
        protected SpiritualNoteRepositoryContract $noteRepository,
        protected UserRepositoryContract $userRepository,
        protected NotificationService $notificationService,
    ) {}

    public function listLabels(): Collection
    {
        return $this->labelRepository->all();
    }

    public function createLabel(array $data): SpiritualNoteLabel
    {
        return $this->labelRepository->create($data);
    }

    public function updateLabel(SpiritualNoteLabel $label, array $data): SpiritualNoteLabel
    {
        return $this->labelRepository->update($label, $data);
    }

    public function deleteLabel(SpiritualNoteLabel $label): void
    {
        $this->labelRepository->delete($label);
    }

    public function currentDate(): string
    {
        return now()->toDateString();
    }

    /**
     * Today's active labels merged with whatever the user already saved for
     * today (blank checkbox/note for labels not yet touched).
     *
     * @return array<int, array{label_id: int, label_name: string, is_checked: bool, note: ?string}>
     */
    public function today(User $user): array
    {
        return $this->forDate($user, $this->currentDate());
    }

    /**
     * Active labels merged with whatever the user saved for the given date
     * (blank checkbox/note for labels not yet touched). Also includes any
     * saved entries for labels that have since been deactivated, so a past
     * day still shows what was actually recorded that day.
     *
     * @return array<int, array{label_id: int, label_name: string, is_checked: bool, note: ?string}>
     */
    public function forDate(User $user, string $date): array
    {
        $labels = $this->labelRepository->active();
        $existing = $this->noteRepository->forUserAndDate($user, $date)
            ->keyBy('spiritual_note_label_id');

        $entries = $labels->map(function (SpiritualNoteLabel $label) use ($existing) {
            $entry = $existing->get($label->id);

            return [
                'label_id' => $label->id,
                'label_name' => $label->name,
                'is_checked' => $entry?->is_checked ?? false,
                'note' => $entry?->note,
            ];
        });

        // Eloquent\Collection::except() filters by each model's own primary
        // key, not by the collection's array keys (which here are label
        // IDs) — so it can't be used to exclude already-included labels.
        $activeLabelIds = $labels->pluck('id')->all();
        $extra = $existing->reject(fn ($note, $labelId) => in_array($labelId, $activeLabelIds, true))
            ->map(fn ($note) => [
                'label_id' => $note->spiritual_note_label_id,
                'label_name' => $note->label->name,
                'is_checked' => $note->is_checked,
                'note' => $note->note,
            ]);

        return $entries->concat($extra)->values()->all();
    }

    /**
     * @param  array<int, array{label_id: int, is_checked: bool, note: ?string}>  $entries
     */
    public function saveToday(User $user, array $entries): void
    {
        $date = now()->toDateString();
        $activeLabelIds = $this->labelRepository->active()->pluck('id')->all();

        DB::transaction(function () use ($user, $entries, $date, $activeLabelIds) {
            foreach ($entries as $entry) {
                if (! in_array($entry['label_id'], $activeLabelIds, true)) {
                    continue;
                }

                $this->noteRepository->upsert(
                    $user,
                    $entry['label_id'],
                    $date,
                    $entry['is_checked'],
                    $entry['note'] ?? null,
                );
            }
        });
    }

    /**
     * @return array<int, array{date: string, entries: array<int, array{label_id: int, label_name: string, is_checked: bool, note: ?string}>}>
     */
    public function history(User $user, ?string $from, ?string $to): array
    {
        $to = $to ?? now()->toDateString();
        $from = $from ?? Carbon::parse($to)->subDays(self::DEFAULT_HISTORY_DAYS)->toDateString();

        return $this->noteRepository->forUserBetween($user, $from, $to)
            ->groupBy(fn ($note) => $note->date->toDateString())
            ->map(fn (Collection $notes, string $date) => [
                'date' => $date,
                'entries' => $notes->map(fn ($note) => [
                    'label_id' => $note->spiritual_note_label_id,
                    'label_name' => $note->label->name,
                    'is_checked' => $note->is_checked,
                    'note' => $note->note,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * Notify every active user who hasn't checked a single spiritual note
     * label yet today. Intended to run more than once a day (e.g. 6pm and
     * 10pm reminders) — each run re-notifies whoever is still at zero.
     *
     * @return int the number of users notified
     */
    public function sendReminderToUsersMissingToday(): int
    {
        $userIds = $this->userIdsMissingToday();

        if ($userIds->isEmpty()) {
            return 0;
        }

        $this->notificationService->send(
            new NotificationDTO(
                message: "You haven't filled in today's Spiritual Note yet — take a moment to reflect and check it off.",
                targetType: NotificationTargetType::Specific,
                stageIds: [],
                groupIds: [],
                userIds: $userIds->all(),
                image: null,
                type: NotificationType::SpiritualNoteReminder,
            ),
            null,
        );

        return $userIds->count();
    }

    /**
     * IDs of active users who have not checked a single label today. Empty
     * if no active labels exist (nothing to remind anyone about).
     *
     * @return Collection<int, int>
     */
    private function userIdsMissingToday(): Collection
    {
        if ($this->labelRepository->active()->isEmpty()) {
            return collect();
        }

        $checkedUserIds = $this->noteRepository->userIdsWithCheckedEntryOnDate($this->currentDate());

        return $this->userRepository->activeUserIds()->diff($checkedUserIds)->values();
    }
}
