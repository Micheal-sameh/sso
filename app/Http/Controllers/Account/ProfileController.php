<?php

namespace App\Http\Controllers\Account;

use App\DTOs\UserDTO;
use App\Http\Controllers\BaseController;
use App\Http\Traits\HandlesInertiaResponses;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class ProfileController extends BaseController
{
    use HandlesInertiaResponses;

    public function show(): Response
    {
        return $this->inertia('Account/Show', ['user' => UserDTO::fromModel(Auth::user())->toArray()]);
    }
}
