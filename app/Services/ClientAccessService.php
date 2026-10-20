<?php

namespace App\Services;

use App\Enums\ClientAccessMode;
use App\Models\ClientMeta;
use App\Models\User;
use Illuminate\Support\Collection;

class ClientAccessService
{
    /**
     * Active clients the user may authenticate with and that have a
     * homepage_url to send them to.
     *
     * @return Collection<int, ClientMeta>
     */
    public function accessibleClients(User $user): Collection
    {
        return ClientMeta::query()
            ->whereNotNull('homepage_url')
            ->whereHas('oauthClient', fn ($query) => $query->where('revoked', false))
            ->with(['stages', 'allowedUsers', 'groups', 'excludedUsers'])
            ->orderBy('app_name')
            ->get()
            ->filter(fn (ClientMeta $meta): bool => $this->userCanAccess($user, $meta))
            ->values();
    }

    /**
     * Accessible clients shaped for the "your apps" lists (welcome page and
     * My Apps).
     *
     * @return Collection<int, array{id: string, name: string, logo: ?string, url: string}>
     */
    public function accessibleApps(User $user): Collection
    {
        return $this->accessibleClients($user)
            ->map(fn (ClientMeta $meta): array => [
                'id' => $meta->oauth_client_id,
                'name' => $meta->app_name,
                'logo' => $meta->app_logo_url,
                'url' => $meta->homepage_url,
            ])
            ->values();
    }

    /**
     * Determine whether the given user is allowed to authenticate with the
     * OAuth client described by the given metadata.
     */
    public function userCanAccess(User $user, ClientMeta $meta): bool
    {
        return match ($meta->access_mode) {
            ClientAccessMode::All => true,
            ClientAccessMode::Stage => $meta->stages->contains('id', $user->stage_id),
            ClientAccessMode::Specific => $meta->allowedUsers->contains('id', $user->id),
            ClientAccessMode::Group => $user->loadMissing('groups')->groups
                ->pluck('id')
                ->intersect($meta->groups->pluck('id'))
                ->isNotEmpty(),
            ClientAccessMode::AllExceptUsers => ! $meta->excludedUsers->contains('id', $user->id),
        };
    }
}
