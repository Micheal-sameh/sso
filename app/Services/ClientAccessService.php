<?php

namespace App\Services;

use App\Enums\ClientAccessMode;
use App\Models\ClientMeta;
use App\Models\User;

class ClientAccessService
{
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
        };
    }
}
