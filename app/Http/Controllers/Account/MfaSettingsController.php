<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\BaseController;
use App\Http\Requests\MfaVerifyRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Services\MfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class MfaSettingsController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected MfaService $mfaService,
    ) {}

    public function show(): Response
    {
        return $this->inertia('Account/Mfa', $this->mfaService->getSetupData(Auth::user()));
    }

    public function enable(): RedirectResponse
    {
        session(['mfa_setup_secret' => $this->mfaService->generateSecret()]);

        return $this->redirectRoute('account.mfa');
    }

    public function confirmEnable(MfaVerifyRequest $request): RedirectResponse
    {
        $this->mfaService->enableMfa(Auth::user(), session('mfa_setup_secret'), $request->string('code')->toString());

        session()->forget('mfa_setup_secret');

        return $this->redirectRoute('account.mfa', __('messages.mfa_enabled'));
    }

    public function disable(MfaVerifyRequest $request): RedirectResponse
    {
        $this->mfaService->disableMfa(Auth::user(), $request->string('code')->toString());

        return $this->redirectRoute('account.mfa', __('messages.mfa_disabled'));
    }
}
