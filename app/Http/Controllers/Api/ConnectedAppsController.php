<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Repositories\Contracts\TokenRepositoryContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConnectedAppsController extends BaseController
{
    public function __construct(
        protected TokenRepositoryContract $tokenRepository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->tokenRepository->listForUser($request->user()->id));
    }

    public function revoke(Request $request, string $tokenId): JsonResponse
    {
        $revoked = $this->tokenRepository->revokeForUser($tokenId, $request->user()->id);

        abort_if(! $revoked, 404);

        return response()->json(['message' => 'App access revoked.']);
    }
}
