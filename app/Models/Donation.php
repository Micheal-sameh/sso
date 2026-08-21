<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'fund_id',
        'user_id',
        'donation_plan_id',
        'amount',
        'currency',
        'gateway',
        'status',
        'gateway_reference',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
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

    public function donationPlan(): BelongsTo
    {
        return $this->belongsTo(DonationPlan::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(DonationTransaction::class);
    }
}
