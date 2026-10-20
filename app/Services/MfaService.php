<?php

namespace App\Services;

use App\Exceptions\Auth\InvalidMfaCodeException;
use App\Models\User;
use App\Models\UserMfa;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PragmaRX\Google2FAQRCode\Google2FA;

class MfaService
{
    public const RECOVERY_CODE_COUNT = 8;

    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA;
    }

    /**
     * Generate a new random TOTP secret key.
     */
    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Render an inline SVG QR code for the given user's TOTP secret.
     */
    public function getQrCodeSvg(User $user, string $secret): string
    {
        return $this->google2fa->getQRCodeInline(
            config('mfa.issuer'),
            $user->email,
            $secret,
        );
    }

    /**
     * Build the props needed by the Account/Mfa Inertia page.
     */
    public function getSetupData(User $user): array
    {
        $secret = session('mfa_setup_secret');

        return [
            'mfa' => $user->mfa,
            'qrCodeSvg' => $secret ? $this->getQrCodeSvg($user, $secret) : null,
            'secret' => $secret,
            'recoveryCodes' => $user->mfa?->is_enabled ? $user->mfa->recovery_codes : null,
        ];
    }

    /**
     * Verify the setup code and enable MFA for the user.
     *
     * @throws InvalidMfaCodeException
     */
    public function enableMfa(User $user, string $secret, string $code): void
    {
        if ($this->google2fa->verifyKey($secret, $code) === false) {
            throw new InvalidMfaCodeException;
        }

        UserMfa::updateOrCreate(
            ['user_id' => $user->id],
            [
                'secret' => $secret,
                'is_enabled' => true,
                'recovery_codes' => $this->generateRecoveryCodes(),
            ],
        );
    }

    /**
     * Verify the current TOTP code and disable MFA for the user.
     *
     * @throws InvalidMfaCodeException
     */
    public function disableMfa(User $user, string $code): void
    {
        $mfa = $user->mfa()->firstOrFail();

        if ($this->google2fa->verifyKey($mfa->secret, $code) === false) {
            throw new InvalidMfaCodeException;
        }

        $mfa->update([
            'is_enabled' => false,
            'secret' => null,
            'recovery_codes' => null,
        ]);
    }

    /**
     * Forcibly clear a user's MFA enrollment (admin action, no code required),
     * e.g. when they've lost their device. They'll need to re-enroll.
     */
    public function resetMfa(User $user): void
    {
        $user->mfa?->update([
            'is_enabled' => false,
            'secret' => null,
            'recovery_codes' => null,
        ]);
    }

    /**
     * Verify a TOTP or recovery code for the given user.
     *
     * A TOTP code is accepted once only (replays inside its validity window
     * are rejected), and a recovery code is consumed under a row lock so two
     * concurrent requests can't both redeem it.
     */
    public function verify(User $user, string $code): bool
    {
        $mfa = $user->mfa;

        if ($mfa === null || ! $mfa->is_enabled) {
            return false;
        }

        if ($this->google2fa->verifyKey($mfa->secret, $code)) {
            // Cache::add is atomic: only the first use of this code succeeds.
            // TTL covers the full +/- window (3 x 30s steps) with margin.
            return Cache::add('mfa:used:'.$user->id.':'.$code, true, now()->addSeconds(120));
        }

        return DB::transaction(function () use ($user, $code): bool {
            $locked = UserMfa::where('user_id', $user->id)->lockForUpdate()->first();
            $recoveryCodes = $locked?->recovery_codes ?? [];

            foreach ($recoveryCodes as $i => $candidate) {
                if (hash_equals((string) $candidate, strtoupper($code))) {
                    unset($recoveryCodes[$i]);
                    $locked->update(['recovery_codes' => array_values($recoveryCodes)]);

                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Generate a fresh batch of one-time recovery codes.
     */
    public function generateRecoveryCodes(): array
    {
        return collect(range(1, self::RECOVERY_CODE_COUNT))
            ->map(fn () => $this->randomChunk(4).'-'.$this->randomChunk(4))
            ->all();
    }

    private function randomChunk(int $length): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $chunk = '';

        for ($i = 0; $i < $length; $i++) {
            $chunk .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $chunk;
    }
}
