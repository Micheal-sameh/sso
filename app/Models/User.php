<?php

namespace App\Models;

use App\Enums\FamilyRole;
use App\Enums\UserRole;
use App\Notifications\ResetPasswordNotification;
use App\Traits\HasAuditLog;
use App\Traits\HasCreatedUpdatedBy;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasAuditLog, HasCreatedUpdatedBy, HasFactory, HasRoles, InteractsWithMedia, Notifiable, SoftDeletes;

    protected $fillable = [
        'uuid',
        'membership_code',
        'name',
        'email',
        'phone',
        'date_of_birth',
        'password',
        'role',
        'stage_id',
        'family_id',
        'family_role',
        'is_active',
        'requires_data_update',
        'wants_donation_reminders',
        'donation_reminder_day',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'family_role' => FamilyRole::class,
            'is_active' => 'boolean',
            'requires_data_update' => 'boolean',
            'wants_donation_reminders' => 'boolean',
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function mfa(): HasOne
    {
        return $this->hasOne(UserMfa::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(Token::class, 'user_id');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'user_group');
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function donationPlans(): HasMany
    {
        return $this->hasMany(DonationPlan::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole(Builder $query, UserRole $role): Builder
    {
        return $query->where('role', $role);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('avatar');

        return $media?->getUrl();
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
