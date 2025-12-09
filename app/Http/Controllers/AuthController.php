<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OAuth2Service;
use App\Services\PKCEHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Client;

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
     * Show the login form
     */
    public function showLogin(Request $request)
    {
        $data = $request->all();

        // Validate required OAuth2 parameters
        $validator = Validator::make($data, [
            'client_id' => 'required|string',
            'redirect_uri' => 'required|url',
            'response_type' => 'required|in:code',
            'scope' => 'nullable|string',
            'state' => 'nullable|string',
            'nonce' => 'nullable|string',
            'code_challenge' => 'nullable|string',
            'code_challenge_method' => 'nullable|in:plain,S256',
        ]);

        if ($validator->fails()) {
            return view('auth.error', [
                'error' => 'invalid_request',
                'error_description' => 'Invalid authorization request parameters',
            ]);
        }

        // Validate client
        $client = Client::where('id', $data['client_id'])->first();
        if (! $client) {
            return view('auth.error', [
                'error' => 'invalid_client',
                'error_description' => 'Unknown client',
            ]);
        }

        // Validate redirect URI
        if (! in_array($data['redirect_uri'], explode(',', $client->redirect))) {
            return view('auth.error', [
                'error' => 'invalid_request',
                'error_description' => 'Invalid redirect URI',
            ]);
        }

        // Store OAuth2 parameters in session
        Session::put('oauth2_params', $data);

        return view('auth.login', [
            'client' => $client,
            'scopes' => explode(' ', $data['scope'] ?? ''),
        ]);
    }

    /**
     * Process login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Update last login
            $user->update([
                'last_login_at' => Carbon::now(),
                'last_login_ip' => $request->ip(),
            ]);

            $oauth2Params = Session::get('oauth2_params');

            if ($oauth2Params) {
                // Check if consent is needed
                $scopes = explode(' ', $oauth2Params['scope'] ?? '');
                if ($this->needsConsent($user, $oauth2Params['client_id'], $scopes)) {
                    return redirect()->route('consent');
                }

                // Generate authorization code
                return $this->generateAuthCode($user, $oauth2Params);
            }

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials',
        ])->withInput();
    }

    /**
     * Show consent form
     */
    public function showConsent(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $oauth2Params = Session::get('oauth2_params');
        if (! $oauth2Params) {
            return redirect()->route('login');
        }

        $client = Client::find($oauth2Params['client_id']);
        $scopes = explode(' ', $oauth2Params['scope'] ?? '');

        return view('auth.consent', [
            'client' => $client,
            'scopes' => $scopes,
        ]);
    }

    /**
     * Process consent
     */
    public function consent(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $oauth2Params = Session::get('oauth2_params');
        if (! $oauth2Params) {
            return redirect()->route('login');
        }

        if ($request->input('consent') !== 'approve') {
            // User denied consent
            $redirectUri = $oauth2Params['redirect_uri'].'?'.http_build_query([
                'error' => 'access_denied',
                'error_description' => 'User denied authorization',
                'state' => $oauth2Params['state'] ?? null,
            ]);

            Session::forget('oauth2_params');

            return redirect($redirectUri);
        }

        $user = Auth::user();

        return $this->generateAuthCode($user, $oauth2Params);
    }

    /**
     * Generate authorization code and redirect
     */
    private function generateAuthCode(User $user, array $oauth2Params)
    {
        $code = $this->oauth2Service->createAuthCode([
            'user_id' => $user->id,
            'client_id' => $oauth2Params['client_id'],
            'scopes' => explode(' ', $oauth2Params['scope'] ?? []),
            'redirect_uri' => $oauth2Params['redirect_uri'],
            'code_challenge' => $oauth2Params['code_challenge'] ?? null,
            'code_challenge_method' => $oauth2Params['code_challenge_method'] ?? 'plain',
        ]);

        $redirectUri = $oauth2Params['redirect_uri'].'?'.http_build_query([
            'code' => $code,
            'state' => $oauth2Params['state'] ?? null,
        ]);

        Session::forget('oauth2_params');

        return redirect($redirectUri);
    }

    /**
     * Check if user needs to give consent
     */
    private function needsConsent(User $user, string $clientId, array $scopes): bool
    {
        // For simplicity, always require consent for now
        // In production, you might store user consents and check here
        return true;
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        Session::flush();

        // Redirect to post-logout redirect URI if provided
        $postLogoutRedirectUri = $request->input('post_logout_redirect_uri');
        if ($postLogoutRedirectUri) {
            return redirect($postLogoutRedirectUri);
        }

        return redirect('/');
    }

    /**
     * Show MFA setup (placeholder)
     */
    public function showMfaSetup()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        // Placeholder for MFA setup
        return view('auth.mfa-setup');
    }

    /**
     * Process MFA setup (placeholder)
     */
    public function setupMfa(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        // Placeholder for MFA setup logic
        $user = Auth::user();
        $user->update([
            'mfa_enabled' => true,
            'mfa_secret' => 'placeholder_secret', // Generate actual secret
        ]);

        return redirect('/dashboard')->with('success', 'MFA enabled successfully');
    }
}
