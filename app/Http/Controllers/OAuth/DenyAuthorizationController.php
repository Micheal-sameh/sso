<?php

namespace App\Http\Controllers\OAuth;

use Illuminate\Http\Request;
use Laravel\Passport\Exceptions\InvalidAuthTokenException;
use Laravel\Passport\Http\Controllers\DenyAuthorizationController as PassportDenyAuthorizationController;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class DenyAuthorizationController extends PassportDenyAuthorizationController
{
    /**
     * Deny the authorization request — see ApproveAuthorizationController
     * for why InvalidAuthTokenException must be caught here rather than
     * relying on the bootstrap/app.php render() callback.
     */
    public function deny(Request $request, ResponseInterface $psrResponse): Response
    {
        try {
            return parent::deny($request, $psrResponse);
        } catch (InvalidAuthTokenException) {
            $message = 'That authorization request has expired or was already used. Please try signing in again from the app.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 419);
            }

            return back()->with('flash', ['type' => 'error', 'message' => $message]);
        }
    }
}
