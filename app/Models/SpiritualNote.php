<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpiritualNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'spiritual_note_label_id',
        'date',
        'is_checked',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_checked' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function label(): BelongsTo
    {
        return $this->belongsTo(SpiritualNoteLabel::class, 'spiritual_note_label_id');
    }
}
