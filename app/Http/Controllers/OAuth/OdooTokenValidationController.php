<?php

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passport\AccessToken;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

/**
 * Odoo's auth_oauth module calls its configured "Validation Endpoint" with the
 * access token as a `?access_token=` query parameter rather than an
 * Authorization header, so it can't use the standard auth:api guard. This
 * replicates Passport's own bearer-token validation (see
 * Laravel\Passport\Http\Middleware\ValidateToken) against a token sourced
 * from the query string instead.
 */
class OdooTokenValidationController extends BaseController
{
    public function __construct(
        protected ResourceServer $server,
    ) {}

    public function validate(Request $request): JsonResponse
    {
        $accessToken = $request->query('access_token');

        abort_if(! is_string($accessToken) || $accessToken === '', 401, 'Missing access_token.');

        $psrRequest = (new PsrHttpFactory)->createRequest($request)
            ->withHeader('Authorization', "Bearer {$accessToken}");

        try {
            $psrRequest = $this->server->validateAuthenticatedRequest($psrRequest);
        } catch (OAuthServerException) {
            abort(401, 'Invalid or expired access token.');
        }

        $token = AccessToken::fromPsrRequest($psrRequest);

        $user = User::find((int) $token->oauth_user_id);

        abort_if($user === null, 401, 'Invalid or expired access token.');

        // The token arrives in the query string, so keep it (and the response)
        // out of caches and out of any Referer header. Also make sure the
        // reverse proxy's access log drops the access_token parameter.
        return response()->json([
            'user_id' => $user->uuid,
            'email' => $user->email,
            'name' => $user->name,
        ])->withHeaders([
            'Cache-Control' => 'no-store, private',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }
}
