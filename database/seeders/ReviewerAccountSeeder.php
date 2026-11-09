<?php

namespace Database\Seeders;

use App\Enums\FamilyRole;
use App\Enums\UserRole;
use App\Models\Family;
use App\Models\Group;
use App\Models\Stage;
use App\Models\User;
use App\Models\UserMfa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Creates (or resets) the app-store / Google review account: an active,
 * email-verified `user` with no MFA, a family and a group so the account
 * has something to show.
 *
 * Deliberately NOT called from DatabaseSeeder. Run it explicitly:
 *
 *   php artisan db:seed --class=ReviewerAccountSeeder --force
 *
 * Optionally override with REVIEWER_MEMBERSHIP_CODE / REVIEWER_PASSWORD.
 *
 * Note: MFA_REQUIRED=true still confines a user without MFA to the setup
 * page (EnsureMfaEnabled), so this account only works end-to-end while
 * MFA_REQUIRED=false or once a per-account exemption exists.
 * Delete or deactivate the account when the review is finished.
 */
class ReviewerAccountSeeder extends Seeder
{
    public function run(): void
    {
        // Defaults are the credentials given to Google's reviewers; env vars override.
        $code = (string) env('REVIEWER_MEMBERSHIP_CODE', 'E1C1F1NR10');
        $password = (string) env('REVIEWER_PASSWORD', 'test@avarewase');

        if ($code === '' || strlen($password) < 12) {
            $this->command?->warn('Skipping reviewer account: set REVIEWER_MEMBERSHIP_CODE and a REVIEWER_PASSWORD of at least 12 characters.');

            return;
        }

        if (User::where('membership_code', $code)->where('role', '!=', UserRole::User)->exists()) {
            $this->command?->error('That membership code belongs to a non-"user" account; refusing to touch it.');

            return;
        }

        $stage = Stage::where('code', 'adult')->first() ?? Stage::query()->orderByDesc('sort_order')->first();

        if ($stage === null) {
            $this->command?->error('No stages found. Seed stages first (StageSeeder).');

            return;
        }

        DB::transaction(function () use ($code, $password, $stage) {
            $reviewer = User::withTrashed()->firstOrNew(['membership_code' => $code]);

            if (! $reviewer->exists) {
                $reviewer->uuid = Str::uuid()->toString();
            }

            $family = $reviewer->family ?? Family::create();

            $reviewer->forceFill([
                'name' => 'Google Reviewer',
                'email' => env('REVIEWER_EMAIL') ?: null,
                'phone' => null,
                'date_of_birth' => '1985-01-01',
                'password' => $password,
                'role' => UserRole::User,
                'stage_id' => $stage->id,
                'family_id' => $family->id,
                'family_role' => FamilyRole::Father,
                'is_active' => true,
                'requires_data_update' => false,
                'email_verified_at' => now(),
                'deleted_at' => null,
            ])->save();

            // No second factor of any kind on this account.
            UserMfa::where('user_id', $reviewer->id)->delete();

            // Sample family members so the profile/family pages have content.
            $members = [
                ['Sample Spouse', FamilyRole::Mother, '1987-03-15', 'adult'],
                ['Sample Child', FamilyRole::Child, '2014-06-20', 'pri'],
            ];

            foreach ($members as $i => [$name, $role, $dob, $stageCode]) {
                $member = User::withTrashed()->firstOrNew([
                    'membership_code' => $code.'-'.($i + 1),
                ]);

                if (! $member->exists) {
                    $member->uuid = Str::uuid()->toString();
                }

                $member->forceFill([
                    'name' => $name,
                    'email' => null,
                    'date_of_birth' => $dob,
                    // Random: sample members are display-only, nobody logs in as them.
                    'password' => Str::random(40),
                    'role' => UserRole::User,
                    'stage_id' => Stage::where('code', $stageCode)->value('id') ?? $stage->id,
                    'family_id' => $family->id,
                    'family_role' => $role,
                    'is_active' => true,
                    'requires_data_update' => false,
                    'deleted_at' => null,
                ])->save();
            }

            $group = Group::firstOrCreate(
                ['name' => 'Sample Group'],
                ['description' => 'Sample group for the review account.'],
            );
            $group->users()->syncWithoutDetaching([$reviewer->id]);
        });

        $this->command?->info("Reviewer account ready. Membership code: {$code}");
    }
}
