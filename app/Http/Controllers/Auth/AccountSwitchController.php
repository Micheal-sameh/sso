<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Traits\HandlesInertiaResponses;
use App\Services\AccountSwitcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AccountSwitchController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(protected AccountSwitcher $switcher) {}

    /** Sign out the active account (keeping the others) to log in with another. */
    public function add(Request $request): RedirectResponse
    {
        $intended = $this->safeIntended($request);

        if ($request->user() !== null) {
            $this->switcher->remember($request->user());
        }

        $this->switcher->signOutKeepingList();

        if ($intended !== null) {
            session(['url.intended' => url($intended)]);
        }

        return redirect()->route('login');
    }

    /** Switch to another account already signed in on this browser. */
    public function switch(Request $request): RedirectResponse
    {
        $request->validate(['user_id' => ['required', 'integer']]);

        if (! $this->switcher->switchTo($request->integer('user_id'))) {
            return $this->redirectRoute('login', __('messages.invalid_or_expired_verification_session'), 'error');
        }

        $intended = $this->safeIntended($request);

        return $intended !== null
            ? redirect($intended)
            : $this->redirectIntended('account.show');
    }

    /** Only a same-site /oauth/authorize path is honoured (no open redirect). */
    private function safeIntended(Request $request): ?string
    {
        $intended = (string) $request->input('intended', '');

        return preg_match('#^/oauth/authorize(\?\S*)?$#', $intended) === 1 ? $intended : null;
    }
}
