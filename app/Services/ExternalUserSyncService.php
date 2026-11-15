<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Exceptions\Auth\OAuthClientException;
use App\Models\AuditLog;
use App\Models\ExternalSyncClient;
use App\Models\Group;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\ClientRepository;

class ExternalUserSyncService
{
    public function __construct(
        protected ClientRepository $clientRepository,
        protected OAuthClientService $oauthClientService,
    ) {}

    /**
     * Create or update a user (matched by membership code) on behalf of a
     * registered OAuth client, together with their stage and group memberships.
     * Roles are never touched: new users are always plain `user`s.
     *
     * Stages are matched by code (created if unknown, using `stage_name` or the
     * code as the display name); groups are matched by name and created if missing.
     * `groups`, when present, replaces the user's memberships.
     *
     * @return array{user: User, created: bool}
     *
     * @throws OAuthClientException
     * @throws ValidationException
     */
    public function sync(string $clientId, string $clientSecret, array $data): array
    {
        $client = $this->clientRepository->find($clientId);

        if ($client === null || $client->revoked || ! Hash::check($clientSecret, $client->secret)) {
            throw new OAuthClientException(__('messages.invalid_client_credentials'));
        }

        // Valid credentials alone are not enough: only explicitly allowlisted clients may sync users.
        if (! ExternalSyncClient::isAllowed((string) $client->getKey())) {
            throw OAuthClientException::forbidden(__('messages.sync_client_not_allowed'));
        }

        return DB::transaction(function () use ($data, $client): array {
            $user = User::withTrashed()->where('membership_code', $data['membership_code'])->first();
            $created = $user === null;

            // Never silently modify (or shadow) a deleted account.
            if ($user?->trashed()) {
                throw ValidationException::withMessages(['membership_code' => 'This membership code belongs to a deleted user.']);
            }

            // Only plain users may be modified from outside; never admins, secretaries or super admins.
            if (! $created && ! $this->isPlainUser($user)) {
                throw OAuthClientException::forbidden(__('messages.sync_privileged_user'));
            }

            $this->assertEmailAvailable($data['email'] ?? null, $user);

            if ($created && empty($data['name'])) {
                throw ValidationException::withMessages(['name' => 'The name field is required when creating a user.']);
            }

            $attributes = array_intersect_key($data, array_flip(['name', 'email', 'phone', 'date_of_birth', 'is_active']));

            if (! empty($data['stage'])) {
                $attributes['stage_id'] = Stage::firstOrCreate(
                    ['code' => $data['stage']],
                    ['name' => $data['stage_name'] ?? $data['stage']],
                )->id;
            } elseif ($created) {
                $stageId = Stage::query()->orderBy('sort_order')->value('id');

                if ($stageId === null) {
                    throw ValidationException::withMessages(['stage' => 'A stage is required when creating a user.']);
                }

                $attributes['stage_id'] = $stageId;
            }

            if ($created) {
                $user = new User([
                    ...$attributes,
                    'membership_code' => $data['membership_code'],
                    'uuid' => Str::uuid()->toString(),
                    // Never synced from outside: random and unusable until the
                    // user sets their own via a reset link.
                    'password' => Str::random(40),
                    'role' => UserRole::User,
                ]);
                $user->save();
            } else {
                $wasActive = $user->is_active;

                $user->fill($attributes)->save();

                // A deactivated account must lose access it already holds (tokens,
                // sessions), exactly as when an admin deactivates it in the panel.
                if ($wasActive && ! $user->is_active) {
                    $this->revokeAccess($user);
                }
            }

            if (array_key_exists('groups', $data)) {
                $groupIds = collect($data['groups'])
                    ->map(fn (string $name) => Group::firstOrCreate(['name' => $name])->id)
                    ->all();

                $user->groups()->sync($groupIds);
            }

            $user = $user->fresh(['stage', 'groups']);

            // The model's own audit rows carry no actor (no logged-in user), so
            // record which OAuth client made this change and which fields it sent.
            AuditLog::create([
                'user_id' => null,
                'action' => $created ? 'external_sync_created' : 'external_sync_updated',
                'model_type' => User::class,
                'model_id' => $user->getKey(),
                'old_values' => null,
                'new_values' => [
                    'client_id' => (string) $client->getKey(),
                    'fields' => array_keys(Arr::except($data, ['membership_code'])),
                    'groups' => $user->groups->pluck('name')->all(),
                ],
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ]);

            if (! $created) {
                $this->oauthClientService->notifyClientsUserUpdated($user);
            }

            return ['user' => $user, 'created' => $created];
        });
    }

    /**
     * Cut off everything a deactivated user could still authenticate with:
     * OAuth access and refresh tokens, remember-me cookies and (when stored
     * in the database) browser sessions.
     */
    private function revokeAccess(User $user): void
    {
        $tokenIds = $user->tokens()->pluck('id');

        $user->tokens()->update(['revoked' => true]);
        DB::table('oauth_refresh_tokens')->whereIn('access_token_id', $tokenIds)->update(['revoked' => true]);

        $user->forceFill(['remember_token' => Str::random(60)])->save();

        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->delete();
        }
    }

    private function isPlainUser(User $user): bool
    {
        return $user->role === UserRole::User
            && $user->roles->pluck('name')->diff([UserRole::User->value])->isEmpty();
    }

    private function assertEmailAvailable(?string $email, ?User $existing): void
    {
        if (empty($email)) {
            return;
        }

        $taken = User::withTrashed()
            ->where('email', $email)
            ->when($existing, fn ($q) => $q->where('id', '!=', $existing->id))
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages(['email' => 'The email has already been taken.']);
        }
    }
}
