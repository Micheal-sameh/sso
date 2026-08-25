<?php

namespace Tests\Feature;

use App\Models\SpiritualNote;
use App\Models\SpiritualNoteLabel;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SpiritualNoteTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_sees_active_labels_blank_for_today(): void
    {
        // Any other active labels already in the (shared, transaction-rolled-back) database
        // would make the exact-count assertions below unreliable, so isolate within this test.
        SpiritualNoteLabel::query()->update(['is_active' => false]);

        $user = User::factory()->create(['requires_data_update' => false, 'wants_donation_reminders' => false]);
        SpiritualNoteLabel::factory()->create(['name' => 'Prayed', 'is_active' => true]);
        SpiritualNoteLabel::factory()->create(['name' => 'Inactive Label', 'is_active' => false]);

        $response = $this->actingAs($user)->get('/spiritual-notes');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('entries', 1)
            ->where('entries.0.label_name', 'Prayed')
            ->where('entries.0.is_checked', false)
            ->where('date', now()->toDateString())
        );
    }

    public function test_user_can_save_todays_entries_and_they_persist(): void
    {
        $user = User::factory()->create(['requires_data_update' => false, 'wants_donation_reminders' => false]);
        $label = SpiritualNoteLabel::factory()->create(['name' => 'Prayed', 'is_active' => true]);

        $this->actingAs($user)->post('/spiritual-notes', [
            'entries' => [
                ['label_id' => $label->id, 'is_checked' => true, 'note' => 'Morning prayers'],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('spiritual_notes', [
            'user_id' => $user->id,
            'spiritual_note_label_id' => $label->id,
            'is_checked' => true,
            'note' => 'Morning prayers',
        ]);

        // Saving again for the same day updates the existing row rather than duplicating it.
        $this->actingAs($user)->post('/spiritual-notes', [
            'entries' => [
                ['label_id' => $label->id, 'is_checked' => false, 'note' => 'Changed my mind'],
            ],
        ]);

        $this->assertSame(1, SpiritualNote::where('user_id', $user->id)->where('spiritual_note_label_id', $label->id)->count());
        $this->assertDatabaseHas('spiritual_notes', [
            'user_id' => $user->id,
            'spiritual_note_label_id' => $label->id,
            'is_checked' => false,
            'note' => 'Changed my mind',
        ]);
    }

    public function test_saving_an_inactive_labels_entry_is_ignored(): void
    {
        $user = User::factory()->create(['requires_data_update' => false, 'wants_donation_reminders' => false]);
        $label = SpiritualNoteLabel::factory()->create(['is_active' => false]);

        $this->actingAs($user)->post('/spiritual-notes', [
            'entries' => [
                ['label_id' => $label->id, 'is_checked' => true, 'note' => 'Should not save'],
            ],
        ]);

        $this->assertDatabaseMissing('spiritual_notes', [
            'spiritual_note_label_id' => $label->id,
        ]);
    }

    public function test_users_entries_are_isolated_from_each_other(): void
    {
        SpiritualNoteLabel::query()->update(['is_active' => false]);

        $userA = User::factory()->create(['requires_data_update' => false, 'wants_donation_reminders' => false]);
        $userB = User::factory()->create(['requires_data_update' => false, 'wants_donation_reminders' => false]);
        $label = SpiritualNoteLabel::factory()->create(['is_active' => true]);

        $this->actingAs($userA)->post('/spiritual-notes', [
            'entries' => [
                ['label_id' => $label->id, 'is_checked' => true, 'note' => "A's note"],
            ],
        ]);

        $response = $this->actingAs($userB)->get('/spiritual-notes');

        $response->assertInertia(fn ($page) => $page
            ->where('entries.0.is_checked', false)
            ->where('entries.0.note', null)
        );
    }

    public function test_viewing_a_past_date_shows_what_was_saved_that_day(): void
    {
        $user = User::factory()->create(['requires_data_update' => false, 'wants_donation_reminders' => false]);
        $label = SpiritualNoteLabel::factory()->create(['name' => 'Prayed', 'is_active' => true]);
        $yesterday = now()->subDay()->toDateString();

        SpiritualNote::create([
            'user_id' => $user->id,
            'spiritual_note_label_id' => $label->id,
            'date' => $yesterday,
            'is_checked' => true,
            'note' => 'Yesterday note',
        ]);

        $response = $this->actingAs($user)->get('/spiritual-notes?date='.$yesterday);

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('date', $yesterday)
            ->where('today', now()->toDateString())
        );
    }

    public function test_cannot_edit_a_past_date_even_if_the_request_claims_it(): void
    {
        $user = User::factory()->create(['requires_data_update' => false, 'wants_donation_reminders' => false]);
        $label = SpiritualNoteLabel::factory()->create(['is_active' => true]);
        $yesterday = now()->subDay()->toDateString();

        $this->actingAs($user)->post('/spiritual-notes', [
            'date' => $yesterday,
            'entries' => [
                ['label_id' => $label->id, 'is_checked' => true, 'note' => 'Trying to backdate'],
            ],
        ]);

        $this->assertDatabaseHas('spiritual_notes', [
            'spiritual_note_label_id' => $label->id,
            'date' => now()->toDateString(),
        ]);
        $this->assertDatabaseMissing('spiritual_notes', [
            'spiritual_note_label_id' => $label->id,
            'date' => $yesterday,
        ]);
    }

    public function test_a_future_date_is_clamped_to_today(): void
    {
        $user = User::factory()->create(['requires_data_update' => false, 'wants_donation_reminders' => false]);
        $tomorrow = now()->addDay()->toDateString();

        $response = $this->actingAs($user)->get('/spiritual-notes?date='.$tomorrow);

        $response->assertInertia(fn ($page) => $page->where('date', now()->toDateString()));
    }

    public function test_non_admin_cannot_manage_labels(): void
    {
        $user = User::factory()->create(['requires_data_update' => false, 'wants_donation_reminders' => false]);

        $this->actingAs($user)->get('/admin/spiritual-note-labels')->assertForbidden();
    }
}
