<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\BaseController;
use App\Http\Traits\HandlesInertiaResponses;
use App\Services\ClientAccessService;
use Illuminate\Http\Request;
use Inertia\Response;

class MyAppsController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected ClientAccessService $clientAccessService,
    ) {}

    public function index(Request $request): Response
    {
        return $this->inertia('Account/MyApps', [
            'apps' => $this->clientAccessService->accessibleApps($request->user()),
        ]);
    }
}
