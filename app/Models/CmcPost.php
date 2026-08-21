<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CmcPost extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'text',
        'url',
        'url_title',
        'url_description',
        'url_image',
        'created_by',
    ];

    protected $appends = [
        'image_url',
    ];

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

    public function getImageUrlAttribute(): ?string
    {
        return $this->getFirstMedia('image')?->getUrl();
    }
}
