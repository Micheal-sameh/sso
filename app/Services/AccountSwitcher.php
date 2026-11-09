<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Remembers the accounts that have fully signed in (password + MFA) on this
 * browser session so the user can switch between them without signing out.
 *
 * The app still sees exactly one authenticated user at a time; this only keeps
 * a short list in the server-side session and swaps the active user on request.
 * Any role can be remembered (like switching Google accounts); an entry is dropped
 * if the account is deactivated or its password or role changes.
 */
class AccountSwitcher
{
    private const KEY = 'remembered_accounts';

    private const MAX_ACCOUNTS = 5;

    /** Record a user who has just completed a full login. */
    public function remember(User $user): void
    {
        $accounts = (array) session(self::KEY, []);

        // Bounded list: forget the oldest account when a new one is added.
        if (! isset($accounts[$user->id]) && count($accounts) >= self::MAX_ACCOUNTS) {
            unset($accounts[array_key_first($accounts)]);
        }

        // Fingerprint of credentials + role at sign-in: a later password or role
        // change invalidates the entry, so a stale session can't switch back in.
        $accounts[$user->id] = $this->fingerprint($user);

        session([self::KEY => $accounts]);
    }

    /**
     * Other accounts the browser can switch to right now (excluding $currentId).
     * Entries that are no longer valid are dropped.
     *
     * @return array<int, User>
     */
    public function others(?int $currentId): array
    {
        $accounts = (array) session(self::KEY, []);
        $valid = [];
        $result = [];

        foreach (User::whereIn('id', array_keys($accounts))->get() as $user) {
            if (! $this->isUsable($user, $accounts[$user->id] ?? null)) {
                continue;
            }

            $valid[$user->id] = $accounts[$user->id];

            if ($user->id !== $currentId) {
                $result[] = $user;
            }
        }

        session([self::KEY => $valid]);

        return $result;
    }

    /** Make a remembered account the active one. Returns false if not allowed. */
    public function switchTo(int $userId): bool
    {
        $accounts = (array) session(self::KEY, []);
        $user = User::find($userId);

        if ($user === null || ! $this->isUsable($user, $accounts[$userId] ?? null)) {
            unset($accounts[$userId]);
            session([self::KEY => $accounts]);

            return false;
        }

        Auth::login($user);
        session()->regenerate();

        return true;
    }

    /**
     * Sign the active account out but keep the remembered list, so the next
     * login adds to it instead of replacing it.
     */
    public function signOutKeepingList(): void
    {
        Auth::logout();
        session()->regenerate();
    }

    private function isUsable(User $user, ?string $fingerprint): bool
    {
        return $fingerprint !== null
            && $user->is_active
            && hash_equals($fingerprint, $this->fingerprint($user));
    }

    private function fingerprint(User $user): string
    {
        return hash('sha256', $user->id.'|'.$user->password.'|'.$user->role?->value);
    }
}
