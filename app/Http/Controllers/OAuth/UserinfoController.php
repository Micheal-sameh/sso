<?php

namespace App\Http\Controllers\OAuth;

use App\DTOs\UserDTO;
use App\Http\Controllers\BaseController;
use App\Http\Traits\HandlesApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserinfoController extends BaseController
{
    use HandlesApiResponses;

    public function me(Request $request): JsonResponse
    {
        return $this->ok(UserDTO::fromModel($request->user())->toOidcArray());
    }
}
