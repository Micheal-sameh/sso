<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginUserRequest;
use App\Models\ArToken;
use Illuminate\Support\Facades\Auth;

class UserController extends BaseController
{
    public function login(LoginUserRequest $request)
    {
        $input = $request->validated();

        $credentials = $request->only('membership_code', 'password');
        if (! Auth::attempt($credentials)) {

            return $this->apiErrorResponse('credentials do not match', 401);
        }
        $user = auth()->user();

        // if (isset($input['fcm_token'])) {
        //     $user->update(['fcm_token' => $input['fcm_token']]);
        // }

        $arTokens = ArToken::where('user_id', $user->id)->first();

        $app_name = $this->getAppName($input['app_name']);
        if ($arTokens) {
            if ($arTokens->{$app_name} == null) {
                // Create ArToken with encrypted fields
                $arTokens->update([
                    "$app_name" => $this->token(),
                ]);
            }
        } else {
            ArToken::create([
                'user_id' => $user->id,
                "$app_name" => $this->token(),
            ]);
        }

        return $this->apiresponse([
            'user' => $user,
            'app_token' => $arTokens->{$app_name},
        ]);

    }

    public function getAppName($app_name)
    {
        return match ($app_name) {
            'rashi' => 'rashi',
            'visits' => 'visits',
            'eftkad' => 'eftkad',
            default => null,
        };
    }

    public function token()
    {
        return bin2hex(random_bytes(64));
    }
}
