<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Passport\Client;

class ClientMeta extends Model
{
    protected $table = 'client_meta';

    protected $fillable = [
        'oauth_client_id',
        'app_name',
        'app_logo_url',
        'homepage_url',
        'is_internal',
        'is_verified',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
            'is_verified' => 'boolean',
        ];
    }

    public function oauthClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'oauth_client_id');
    }

    public function getLogoUrlAttribute(): string
    {
        return $this->app_logo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($this->app_name).'&background=B8860B&color=fff';
    }
}
