<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ApiForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ForgetPasswordController extends BaseController
{
    public function sendResetLinkEmail(ApiForgotPasswordRequest $request)
    {
        if (! User::where('email', $request->email)->exists()) {
            return $this->apiErrorResponse('This email cannot reset password, please refer to the admins');
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? $this->apiResponse(message: __('messages.password_reset_link_sent'), status_code: 200)
            : $this->apiErrorResponse(__('messages.password_reset_link_failed'));
    }

    /**
     * Show the reset password form
     */
    public function showResetPasswordForm(Request $request, $token = null)
    {
        return view('auth.reset-password')->with([
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Reset the user's password
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __('messages.password_reset_success'))
            : back()->withErrors(['email' => [__('messages.password_reset_failed')]]);
    }
}
