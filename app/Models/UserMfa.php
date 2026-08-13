<?php

namespace App\Models;

use App\Casts\EncryptedCast;
use App\Casts\EncryptedJsonCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMfa extends Model
{
    protected $table = 'user_mfa';

    protected $fillable = [
        'user_id',
        'secret',
        'is_enabled',
        'recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'secret' => EncryptedCast::class,
            'is_enabled' => 'boolean',
            'recovery_codes' => EncryptedJsonCast::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
