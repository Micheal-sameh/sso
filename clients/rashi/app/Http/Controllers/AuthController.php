<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Show the login page
     */
    public function showLogin(Request $request)
    {
        return view('auth.login');
    }

    /**
     * Redirect to SSO server
     */
    public function login(Request $request)
    {
        return Socialite::driver('sso')->redirect();
    }

    /**
     * Handle SSO callback
     */
    public function callback(Request $request)
    {
        try {
            $ssoUser = Socialite::driver('sso')->user();

            // Find or create local user
            $user = User::where('sso_id', $ssoUser->getId())
                ->orWhere('email', $ssoUser->getEmail())
                ->first();

            if (! $user) {
                $user = User::create([
                    'name' => $ssoUser->getName(),
                    'email' => $ssoUser->getEmail(),
                    'sso_id' => $ssoUser->getId(),
                    'password' => Hash::make(Str::random(32)), // Random password since SSO handles auth
                    'email_verified_at' => $ssoUser->user['email_verified'] ? now() : null,
                ]);
            } else {
                // Update user info from SSO
                $user->update([
                    'name' => $ssoUser->getName(),
                    'sso_id' => $ssoUser->getId(),
                    'email_verified_at' => $ssoUser->user['email_verified'] ? now() : null,
                ]);
            }

            Auth::login($user, true);

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            return redirect('/login')->withErrors([
                'sso' => 'Authentication failed. Please try again.',
            ]);
        }
    }

    /**
     * Logout locally and redirect to SSO logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Redirect to SSO logout with post-logout redirect URI
        $ssoBaseUrl = config('services.sso.base_url');
        $postLogoutRedirectUri = url('/');

        return redirect($ssoBaseUrl.'/logout?post_logout_redirect_uri='.urlencode($postLogoutRedirectUri));
    }
}
