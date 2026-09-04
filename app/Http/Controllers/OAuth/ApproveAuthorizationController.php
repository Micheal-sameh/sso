<?php

namespace App\Http\Controllers\OAuth;

use Illuminate\Http\Request;
use Laravel\Passport\Exceptions\InvalidAuthTokenException;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController as PassportApproveAuthorizationController;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class ApproveAuthorizationController extends PassportApproveAuthorizationController
{
    /**
     * Approve the authorization request, translating a stale/mismatched
     * auth_token into a friendly redirect instead of the generic 403 page
     * Laravel's exception handler produces: it converts any un-flagged
     * AuthorizationException (which InvalidAuthTokenException extends) into
     * AccessDeniedHttpException in prepareException() before our render()
     * callback in bootstrap/app.php ever gets a chance to match it.
     */
    public function approve(Request $request, ResponseInterface $psrResponse): Response
    {
        try {
            return parent::approve($request, $psrResponse);
        } catch (InvalidAuthTokenException) {
            $message = 'That authorization request has expired or was already used. Please try signing in again from the app.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 419);
            }

            return back()->with('flash', ['type' => 'error', 'message' => $message]);
        }
    }
}
