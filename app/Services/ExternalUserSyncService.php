<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Exceptions\Auth\OAuthClientException;
use App\Models\ExternalSyncClient;
use App\Models\Group;
use App\Models\Stage;
use App\Models\User;
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

        return DB::transaction(function () use ($data): array {
            $user = User::withTrashed()->where('membership_code', $data['membership_code'])->first();
            $created = $user === null;

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
                $user->fill($attributes)->save();
            }

            if (array_key_exists('groups', $data)) {
                $groupIds = collect($data['groups'])
                    ->map(fn (string $name) => Group::firstOrCreate(['name' => $name])->id)
                    ->all();

                $user->groups()->sync($groupIds);
            }

            $user = $user->fresh(['stage', 'groups']);

            if (! $created) {
                $this->oauthClientService->notifyClientsUserUpdated($user);
            }

            return ['user' => $user, 'created' => $created];
        });
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
