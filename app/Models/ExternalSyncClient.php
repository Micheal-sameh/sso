<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalSyncClient extends Model
{
    protected $fillable = ['oauth_client_id'];

    public static function isAllowed(string $clientId): bool
    {
        return static::query()->where('oauth_client_id', $clientId)->exists();
    }

    public static function setAllowed(string $clientId, bool $allowed): void
    {
        if ($allowed) {
            static::query()->firstOrCreate(['oauth_client_id' => $clientId]);
        } else {
            static::query()->where('oauth_client_id', $clientId)->delete();
        }
    }
}
