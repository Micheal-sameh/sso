<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(AdminLoginRequest $request)
    {
        $credentials = $request->only('membership_code', 'password');
        $remember = $request->boolean('remember_me');

        // Attempt login
        if (! Auth::attempt($credentials, $remember)) {
            return back()->withErrors(['membership_code' => 'Credentials do not match']);
        }

        $user = auth()->user();
        $request->session()->regenerate();

        return redirect()->intended('/'); // Adjust as needed
    }

    public function logout()
    {
        Auth::logout();

        return to_route('login');
    }
}
