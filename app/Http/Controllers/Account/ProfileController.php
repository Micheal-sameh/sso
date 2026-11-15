<?php

namespace App\Http\Controllers\Account;

use App\DTOs\UserDTO;
use App\Http\Controllers\BaseController;
use App\Http\Traits\HandlesInertiaResponses;
use App\Repositories\Contracts\TokenRepositoryContract;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class ProfileController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(protected TokenRepositoryContract $tokenRepository) {}

    public function show(): Response
    {
        $user = Auth::user();

        // One entry per app the user has authorised, for the profile's
        // "Connected apps" card (revoking still lives on /account/apps).
        $connectedApps = $this->tokenRepository->listForUser($user->id)
            ->map(fn ($token) => $token->toDisplayArray()['client']['name'] ?? null)
            ->filter()
            ->unique()
            ->values();

        return $this->inertia('Account/Show', [
            'user' => UserDTO::fromModel($user)->toArray(),
            'connectedApps' => $connectedApps,
        ]);
    }
}
