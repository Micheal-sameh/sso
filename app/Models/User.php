<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Traits\HasAuditLog;
use App\Traits\HasCreatedUpdatedBy;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Token;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasAuditLog, HasCreatedUpdatedBy, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'uuid',
        'membership_code',
        'name',
        'email',
        'password',
        'avatar',
        'role',
        'is_active',
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
            'is_active' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function mfa(): HasOne
    {
        return $this->hasOne(UserMfa::class);
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(Token::class, 'user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole(Builder $query, UserRole $role): Builder
    {
        return $query->where('role', $role);
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=7B1C2E&color=fff';
    }
}
