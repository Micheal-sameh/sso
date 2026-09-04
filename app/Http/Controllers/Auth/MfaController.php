<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Auth\InvalidMfaCodeException;
use App\Http\Controllers\BaseController;
use App\Http\Requests\MfaVerifyRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Models\User;
use App\Services\MfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class MfaController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected MfaService $mfaService,
    ) {}

    public function showMfa(): Response
    {
        return $this->inertia('Auth/Mfa');
    }

    public function verify(MfaVerifyRequest $request): RedirectResponse
    {
        $user = User::findOrFail($request->session()->get('mfa_user_id'));

        if (! $this->mfaService->verify($user, $request->string('code')->toString())) {
            throw new InvalidMfaCodeException;
        }

        Auth::login($user, (bool) $request->session()->get('mfa_remember', false));

        session()->forget(['mfa_pending', 'mfa_user_id', 'mfa_remember']);

        return $this->redirectIntended('account.show', __('messages.mfa_verified'));
    }
}
