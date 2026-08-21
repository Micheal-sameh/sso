<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DonationPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'fund_id',
        'user_id',
        'amount',
        'currency',
        'gateway',
        'frequency',
        'gateway_token',
        'status',
        'next_charge_at',
        'last_charged_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'next_charge_at' => 'datetime',
            'last_charged_at' => 'datetime',
        ];
    }

    public function fund(): BelongsTo
    {
        return $this->belongsTo(Fund::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }
}
