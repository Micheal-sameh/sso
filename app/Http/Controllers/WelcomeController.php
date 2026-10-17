<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Services\ClientAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends BaseController
{
    public function __construct(
        protected ClientAccessService $clientAccessService,
    ) {}

    /**
     * Landing page. Dashboard users go straight to their account; guests see
     * the marketing page; plain "user" accounts (no dashboard) see the apps
     * they have been given access to so they can sign in to one from here.
     */
    public function __invoke(Request $request): RedirectResponse|Response
    {
        $user = $request->user();

        if ($user !== null && $user->role !== UserRole::User) {
            return redirect()->route('account.show');
        }

        return Inertia::render('Welcome', [
            'apps' => $user === null ? [] : $this->clientAccessService
                ->accessibleClients($user)
                ->map(fn ($meta) => [
                    'id' => $meta->oauth_client_id,
                    'name' => $meta->app_name,
                    'logo' => $meta->app_logo_url,
                    'url' => $meta->homepage_url,
                ])
                ->values(),
        ]);
    }
}
