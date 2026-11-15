<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Api\ExternalUserSyncRequest;
use App\Services\ExternalUserSyncService;
use Illuminate\Http\JsonResponse;

class ExternalUserSyncController extends BaseController
{
    public function __construct(
        protected ExternalUserSyncService $syncService,
    ) {}

    /**
     * Server-to-server upsert of a user (with stage and groups), authenticated
     * by the calling OAuth client's client_id/client_secret.
     */
    public function sync(ExternalUserSyncRequest $request): JsonResponse
    {
        ['user' => $user, 'created' => $created] = $this->syncService->sync(
            $request->string('client_id')->toString(),
            $request->string('client_secret')->toString(),
            $request->safe()->except(['client_id', 'client_secret']),
        );

        return response()->json([
            'created' => $created,
            'user' => [
                'uuid' => $user->uuid,
                'membership_code' => $user->membership_code,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'date_of_birth' => $user->date_of_birth?->toDateString(),
                'is_active' => $user->is_active,
                'stage' => $user->stage?->only(['id', 'name', 'code']),
                'groups' => $user->groups->pluck('name')->values(),
            ],
        ], $created ? 201 : 200);
    }
}
