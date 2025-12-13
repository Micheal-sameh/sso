<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserStatus;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\QrLoginRequest;
use App\Http\Resources\UserResource;
use App\Models\ArToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SSOController extends BaseController
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
        $activeUser = $this->checkUserStatus($user);
        if (! $activeUser) {
            return $this->apiErrorResponse('User is inactive', 403);
        }

        // Get or create user's token for this project
        $arToken = ArToken::firstOrNew(['user_id' => $user->id]);

        if (empty($arToken->{$projectName})) {
            $arToken->{$projectName} = $this->generateToken();
            $arToken->save();
        }
        // else {
        //     return $this->apiResponse(['token' => $arToken->{$projectName}]);
        // }

        // Load relation for resource
        $user->load('arToken');

        // Return user resource with project token
        return $this->apiresponse(new UserResource($user));
    }

    public function Qrlogin(QrLoginRequest $request)
    {
        $projectName = $request->project->name;
        $membership_code = $this->getCredentials($request->qr_code);

        $user = $this->getUserData($membership_code, $projectName);
        // Get or create user's token for this project
        $arToken = ArToken::firstOrNew(['user_id' => $user->id]);

        if (empty($arToken->{$projectName})) {
            $arToken->{$projectName} = $this->generateToken();
            $arToken->save();
        }
        //  else {
        //     return $this->apiResponse(['token' => $arToken->{$projectName}]);
        // }

        // Load relation for resource
        $user->load('arToken');

        // Return user resource with project token
        return $this->apiresponse(new UserResource($user));
    }

    protected function generateToken(): string
    {
        return bin2hex(random_bytes(8));
    }

    private function getCredentials($qr_code)
    {
        $code = explode('|', $qr_code)[0];

        // Parse datetime components
        $minutes = (int) substr($code, 0, 2);
        $hours = (int) substr($code, 2, 2);
        $day = (int) substr($code, 4, 2);
        $month = (int) substr($code, 6, 2);
        $year = 2000 + (int) substr($code, 8, 2);
        $dateTime = Carbon::create($year, $month, $day, $hours, $minutes);
        // Validate datetime is within 5 minutes of current time
        $timeDifference = now()->diffInMinutes($dateTime, false);

        // if ($timeDifference > 5 || $timeDifference < -5) {
        //     throw new \Exception('This code is expired - must be within 5 minutes');
        // }

        // Parse membership components
        $familyNumberLength = (int) substr($code, 10, 1);
        $NR = substr($code, -$familyNumberLength);
        $membershipPart = substr($code, 13, -$familyNumberLength);
        $membership_code = "E1C1F{$membershipPart}NR{$NR}";

        return $membership_code;
    }

    private function getUserData($membership_code, $projectName)
    {
        $user = User::where('membership_code', $membership_code)->first();
        match ($projectName) {
            'rashi' => $user->select('id', 'name', 'membership_code'),
            'visits' => $user->select('id', 'name', 'membership_code'),
            'eftkad' => $user->select('id', 'name', 'membership_code'),
            default => throw new \Exception('Invalid project name'),
        };

        return $user;
    }

    private function checkUserStatus(User $user): bool
    {
        return $user->status == UserStatus::ACTIVE;
    }
}
