<?php

namespace App\Http\Middleware;

use App\DTOs\UserDTO;
use App\Services\AccountSwitcher;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? UserDTO::fromModel($user)->toArray() : null,
            ],
            // Other accounts already signed in on this browser (switchable).
            'other_accounts' => $user
                ? collect(app(AccountSwitcher::class)->others($user->id))
                    ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'membership_code' => $u->membership_code])
                    ->values()
                : [],
            'locale' => app()->getLocale(),
            'direction' => app()->getLocale() === 'ar' ? 'rtl' : 'ltr',
            'flash' => [
                'type' => $request->session()->get('flash.type'),
                'message' => $request->session()->get('flash.message'),
            ],
            'csrf_token' => csrf_token(),
            'turnstile_site_key' => config('services.turnstile.site_key'),
            'errors' => $request->session()->get('errors')
                ? $request->session()->get('errors')->getBag('default')->getMessages()
                : [],
        ];
    }
}
