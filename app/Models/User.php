<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'birth_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => 'integer',
    ];

    public function arToken()
    {
        return $this->hasOne(ArToken::class);
    }

    public function ssoUser()
    {
        return $this->belongsTo(SSOUser::class, 'id', 'user_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'user_groups');
    }

    protected static function booted()
    {
        static::created(function ($user) {
            // Attach default group with id 1 if it exists
            try {
                if (\App\Models\Group::where('id', 1)->exists()) {
                    $user->groups()->syncWithoutDetaching([1]);
                }
            } catch (\Exception $e) {
                // swallow exceptions to avoid breaking user creation flow
                logger()->error('Failed to attach default group to user: '.$e->getMessage());
            }
        });
    }

    public function qrCode()
    {
        // Generate QR code string from membership code
        $code = $this->generateQrCodeString();
        // $qr_code = QrCode::size(150)->generate($code);

        return $code;
    }

    private function generateQrCodeString()
    {
        // Current datetime components
        $now = now();
        $minutes = str_pad($now->minute, 2, '0', STR_PAD_LEFT);
        $hours = str_pad($now->hour, 2, '0', STR_PAD_LEFT);
        $day = str_pad($now->day, 2, '0', STR_PAD_LEFT);
        $month = str_pad($now->month, 2, '0', STR_PAD_LEFT);
        $year = str_pad($now->year - 2000, 2, '0', STR_PAD_LEFT);

        // Parse membership code (format: E1C1F{membershipPart}NR{NR})
        $membership_code = $this->membership_code;
        $membershipPart = '';
        $NR = '';

        if (preg_match('/E1C1F(\d+)NR(\d+)/', strtoupper($membership_code), $matches)) {
            $membershipPart = $matches[1];
            $NR = $matches[2];
        }
        $familyNumberLength = strlen($NR);

        // Build the QR code string
        $code = $minutes.$hours.$day.$month.$year.$familyNumberLength.'11'.$membershipPart.$NR;

        return $code;
    }
}
