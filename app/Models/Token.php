<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Passport\Token as PassportToken;

class Token extends PassportToken
{
    /**
     * Passport's own user() relation reads $this->client->provider to resolve
     * the polymorphic user model, but that access happens on an empty probe
     * instance during eager loading (Builder::getRelation()) before any
     * attributes are set, which throws "Attempt to read property on null".
     * This app only ever has one user provider, so resolve it directly.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Shape this token for the frontend, formatting its timestamps for display
     * instead of leaking Carbon's raw ISO-8601-with-microseconds format.
     */
    public function toDisplayArray(): array
    {
        return [
            ...$this->toArray(),
            'created_at' => $this->created_at?->format('d M Y, H:i'),
            'expires_at' => $this->expires_at?->format('d M Y, H:i'),
        ];
    }
}
