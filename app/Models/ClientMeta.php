<?php

namespace App\Models;

use App\Enums\ClientAccessMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Passport\Client;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ClientMeta extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'client_meta';

    protected $fillable = [
        'oauth_client_id',
        'app_name',
        'homepage_url',
        'is_internal',
        'is_verified',
        'access_mode',
    ];

    protected $appends = [
        'app_logo_url',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
            'is_verified' => 'boolean',
            'access_mode' => ClientAccessMode::class,
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml']);
    }

    public function oauthClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'oauth_client_id');
    }

    /**
     * Stages allowed to authenticate with this client, when access_mode is 'stage'.
     */
    public function stages(): BelongsToMany
    {
        return $this->belongsToMany(
            Stage::class,
            'client_stage_access',
            'oauth_client_id',
            'stage_id',
            'oauth_client_id',
        );
    }

    /**
     * Individual users allowed to authenticate with this client, when access_mode is 'specific'.
     */
    public function allowedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_access',
            'client_id',
            'user_id',
            'oauth_client_id',
        );
    }

    /**
     * Groups allowed to authenticate with this client, when access_mode is 'group'.
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            Group::class,
            'client_group_access',
            'oauth_client_id',
            'group_id',
            'oauth_client_id',
        );
    }

    public function getAppLogoUrlAttribute(): ?string
    {
        return $this->getFirstMedia('logo')?->getUrl();
    }
}
