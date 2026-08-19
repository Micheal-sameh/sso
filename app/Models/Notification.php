<?php

namespace App\Models;

use App\Enums\NotificationTargetType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Notification extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'message',
        'target_type',
        'created_by',
    ];

    protected $appends = [
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'target_type' => NotificationTargetType::class,
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Stages this notification was sent to, when target_type is 'stage'.
     */
    public function stages(): BelongsToMany
    {
        return $this->belongsToMany(Stage::class, 'notification_stage_targets');
    }

    /**
     * Groups this notification was sent to, when target_type is 'group'.
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'notification_group_targets');
    }

    /**
     * Individual users this notification was sent to, when target_type is 'specific'.
     */
    public function targetUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'notification_user_targets');
    }

    /**
     * Users who have read this notification.
     */
    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'notification_reads')->withPivot('read_at');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->getFirstMedia('image')?->getUrl();
    }
}
