<?php

namespace App\Http\Controllers;

use App\Services\OAuth2Service;
use App\Services\PKCEHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected OAuth2Service $oauth2Service;

    protected PKCEHelper $pkceHelper;

    public function __construct(OAuth2Service $oauth2Service, PKCEHelper $pkceHelper)
    {
        $this->oauth2Service = $oauth2Service;
        $this->pkceHelper = $pkceHelper;
    }

    /**
     * Redirect user to SSO login.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginRedirect(Request $request)
    {
        // Generate PKCE code verifier and challenge
        $codeVerifier = $this->pkceHelper->generateVerifier();
        $codeChallenge = $this->pkceHelper->generateChallenge($codeVerifier);

        // Generate state for CSRF protection
        $state = bin2hex(random_bytes(16));

        // Store in session
        $sessionKeys = config('sso.session_keys');
        Session::put([
            $sessionKeys['code_verifier'] => $codeVerifier,
            $sessionKeys['state'] => $state,
        ]);

        // Build authorization URL
        $params = [
            'response_type' => 'code',
            'client_id' => config('sso.client_id'),
            'redirect_uri' => config('sso.redirect_uri'),
            'scope' => implode(' ', config('sso.scopes')),
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ];

        $authorizeUrl = config('sso.endpoints.authorize').'?'.http_build_query($params);

        return redirect($authorizeUrl);
    }

    /**
     * Handle SSO callback.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        try {
            // Validate state
            $sessionKeys = config('sso.session_keys');
            $expectedState = Session::get($sessionKeys['state']);

            if (! $expectedState || $request->state !== $expectedState) {
                Log::warning('Invalid state in SSO callback', [
                    'expected' => $expectedState,
                    'received' => $request->state,
                ]);

                return redirect('/auth/error')->with('error', 'Invalid state parameter');
            }

            // Check for error
            if ($request->has('error')) {
                Log::error('SSO error in callback', [
                    'error' => $request->error,
                    'error_description' => $request->error_description,
                ]);

                return redirect('/auth/error')->with('error', $request->error_description ?? $request->error);
            }

            // Get authorization code
            $code = $request->code;
            if (! $code) {
                return redirect('/auth/error')->with('error', 'No authorization code received');
            }

            // Get code verifier from session
            $codeVerifier = Session::get($sessionKeys['code_verifier']);
            if (! $codeVerifier) {
                return redirect('/auth/error')->with('error', 'Code verifier not found in session');
            }

            // Exchange code for tokens
            $tokens = $this->oauth2Service->exchangeCodeForTokens($code, $codeVerifier);

            // Verify ID Token and extract user data
            if (! isset($tokens['id_token'])) {
                return redirect('/auth/error')->with('error', 'No ID token received');
            }

            $jwkService = app(\App\Services\JWKService::class);
            $payload = $jwkService->verifyIdToken($tokens['id_token']);

            // Create or update user
            $user = $this->oauth2Service->createOrUpdateUser($payload, $tokens);

            // Store tokens in session
            $this->oauth2Service->storeTokensInSession($tokens, $user);

            // Clear temporary session data
            Session::forget([$sessionKeys['state'], $sessionKeys['code_verifier']]);

            // Redirect to dashboard
            return redirect('/dashboard');

        } catch (\Exception $e) {
            Log::error('SSO callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect('/auth/error')->with('error', 'Authentication failed: '.$e->getMessage());
        }
    }

    /**
     * API Login - Authenticate via SSO server API
     */
    public function apiLogin(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'membership_code' => 'required|string',
                'password' => 'required|string',
            ]);

            $membershipCode = $request->input('membership_code');
            $password = $request->input('password');

            // Send credentials to SSO server API
            $response = Http::timeout(30)->post(config('sso.endpoints.token'), [
                'grant_type' => 'password',
                'client_id' => config('sso.client_id'),
                'client_secret' => config('sso.client_secret'),
                'username' => $membershipCode,
                'password' => $password,
                'scope' => implode(' ', config('sso.scopes')),
            ]);

            if (! $response->successful()) {
                Log::warning('SSO API login failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw ValidationException::withMessages([
                    'credentials' => ['Invalid membership code or password'],
                ]);
            }

            $tokenData = $response->json();

            // Verify and decode ID token if present
            $userData = [];
            if (isset($tokenData['id_token'])) {
                $jwkService = app(\App\Services\JWKService::class);
                $userData = $jwkService->verifyIdToken($tokenData['id_token']);
            } else {
                // If no ID token, get user info from access token
                $userInfoResponse = Http::withToken($tokenData['access_token'])
                    ->get(config('sso.endpoints.userinfo'));

                if ($userInfoResponse->successful()) {
                    $userData = $userInfoResponse->json();
                }
            }

            // Create or update user
            $user = $this->oauth2Service->createOrUpdateUser($userData, $tokenData);

            // Create Sanctum token for this project
            $token = $user->createToken('api-token')->plainTextToken;

            Log::info('SSO API login successful', ['user_id' => $user->id, 'membership_code' => $membershipCode]);

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'membership_code' => $user->membership_code,
                    'sub' => $user->sub,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('SSO API login error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
            ], 401);
        }
    }

    /**
     * API Logout - Revoke Sanctum token
     */
    public function apiLogout(Request $request): JsonResponse
    {
        try {
            // Revoke the current access token
            $request->user()->currentAccessToken()->delete();

            // Optionally revoke refresh token from SSO server
            $refreshToken = $request->user()->refresh_token;
            if ($refreshToken) {
                try {
                    $this->oauth2Service->revokeToken($refreshToken);
                } catch (\Exception $e) {
                    Log::warning('SSO API logout: Token revocation failed', ['error' => $e->getMessage()]);
                }
            }

            Log::info('SSO API logout successful', ['user_id' => $request->user()->id]);

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('SSO API logout error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
            ], 500);
        }
    }

    /**
     * Logout user (Web Flow).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        try {
            $sessionKeys = config('sso.session_keys');
            $encryptedRefreshToken = Session::get($sessionKeys['refresh_token']);

            // Revoke refresh token if available
            if ($encryptedRefreshToken) {
                $refreshToken = decrypt($encryptedRefreshToken);
                $this->oauth2Service->revokeToken($refreshToken);
            }

            // Clear session
            $this->oauth2Service->clearSession();

            // Build logout URL
            $logoutUrl = config('sso.endpoints.logout').'?'.http_build_query([
                'post_logout_redirect_uri' => url('/'),
            ]);

            return redirect($logoutUrl);

        } catch (\Exception $e) {
            Log::error('Logout error', [
                'error' => $e->getMessage(),
            ]);

            // Still redirect to logout even if revoke fails
            return redirect(config('sso.endpoints.logout').'?post_logout_redirect_uri='.url('/'));
        }
    }
}
