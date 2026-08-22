<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YoutubeVideo extends Model
{
    protected $fillable = [
        'video_id',
        'title',
        'thumbnail_url',
        'published_at',
        'cmc_post_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function cmcPost(): BelongsTo
    {
        return $this->belongsTo(CmcPost::class);
    }
}
