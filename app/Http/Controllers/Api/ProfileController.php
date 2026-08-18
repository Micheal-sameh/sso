<?php

namespace App\Http\Controllers\Api;

use App\DTOs\UserDTO;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends BaseController
{
    public function me(Request $request): JsonResponse
    {
        return response()->json(UserDTO::fromModel($request->user())->toArray());
    }
}
