<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Auth\InvalidMfaCodeException;
use App\Http\Controllers\BaseController;
use App\Http\Requests\MfaVerifyRequest;
use App\Http\Traits\HandlesInertiaResponses;
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
        $verified = $this->mfaService->verify(Auth::user(), $request->string('code')->toString());

        if (! $verified) {
            throw new InvalidMfaCodeException;
        }

        session()->forget(['mfa_pending', 'mfa_user_id']);

        return $this->redirectIntended('account.show', 'Verified successfully.');
    }
}
