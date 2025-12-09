<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginUserRequest;
use App\Http\Resources\UserResource;
use App\Models\ArToken;
use Illuminate\Support\Facades\Auth;

class UserController extends BaseController
{
    public function login(LoginUserRequest $request)
    {
        $projectName = $request->project->name;
        $credentials = $request->only('membership_code', 'password');

        // Attempt login
        if (! Auth::attempt($credentials)) {
            return $this->apiErrorResponse('Credentials do not match', 401);
        }

        $user = auth()->user();

        // Get or create user's token for this project
        $arToken = ArToken::firstOrNew(['user_id' => $user->id]);

        if (empty($arToken->{$projectName})) {
            $arToken->{$projectName} = $this->generateToken();
            $arToken->save();
        } else {
            return $this->apiResponse(['token' => $arToken->{$projectName}]);
        }

        // Load relation for resource
        $user->load('arToken');

        // Return user resource with project token
        return $this->apiresponse(new UserResource($user));
    }

    /**
     * Generate a secure random token
     */
    protected function generateToken(): string
    {
        return bin2hex(random_bytes(16)); // 32-character token for more security
    }
}
