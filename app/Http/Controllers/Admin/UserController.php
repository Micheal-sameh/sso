<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\CreateUserDTO;
use App\DTOs\UserDTO;
use App\Enums\UserRole;
use App\Http\Controllers\BaseController;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Models\User;
use App\Models\ClientMeta;
use App\Repositories\Contracts\TokenRepositoryContract;
use App\Services\OAuthClientService;
use App\Services\StageService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class UserController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected UserService $userService,
        protected TokenRepositoryContract $tokenRepository,
        protected StageService $stageService,
        protected OAuthClientService $oauthClientService,
    ) {}

    public function index(Request $request): Response
    {
        $users = $this->userService->paginate(
            20,
            $request->string('search')->toString() ?: null,
            $request->enum('role', UserRole::class),
            $request->integer('stage_id') ?: null,
        )->through(fn (User $user) => UserDTO::fromModel($user)->toArray());

        return $this->inertia('Admin/Users/Index', [
            'users' => $users,
            'stages' => $this->stageService->listAll(),
            'filters' => $request->only('search', 'role', 'stage_id'),
        ]);
    }

    public function create(): Response
    {
        return $this->inertia('Admin/Users/Create', [
            'stages' => $this->stageService->listAll(),
        ]);
    }

    public function admins(Request $request): Response
    {
        $admins = $this->userService->paginateAdmins(20, $request->string('search')->toString() ?: null)
            ->through(fn (User $user) => UserDTO::fromModel($user)->toArray());

        return $this->inertia('Admin/Admins/Index', [
            'admins' => $admins,
            'filters' => $request->only('search'),
        ]);
    }

    public function show(User $user): Response
    {
        $specificClients = $this->oauthClientService->listSpecificAccessClients()
            ->map(fn (ClientMeta $meta) => [
                'id' => $meta->oauth_client_id,
                'app_name' => $meta->app_name,
                'has_access' => $meta->allowedUsers->contains('id', $user->id),
            ])
            ->values();

        return $this->inertia('Admin/Users/Show', [
            'viewedUser' => UserDTO::fromModel($user)->toArray(),
            'tokens' => $this->tokenRepository->listForUser($user->id),
            'specificClients' => $specificClients,
        ]);
    }

    public function store(CreateUserRequest $request): RedirectResponse
    {
        $this->userService->create(CreateUserDTO::fromRequest($request));

        return $this->redirectRoute('admin.users.index', 'User created successfully.');
    }

    public function edit(User $user): Response
    {
        return $this->inertia('Admin/Users/Edit', [
            'viewedUser' => UserDTO::fromModel($user)->toArray(),
            'stages' => $this->stageService->listAll(),
        ]);
    }

    public function update(User $user, UpdateUserRequest $request): RedirectResponse
    {
        $this->userService->updateDetails($user, $request->validated());

        return $this->redirectRoute('admin.users.show', 'User updated successfully.', params: ['user' => $user->id]);
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $this->userService->toggleActive($user);

        return $this->redirectBack('User status updated.', 'success');
    }

    public function toggleAdminAccess(User $user): RedirectResponse
    {
        $this->userService->toggleAdminAccess($user);

        return $this->redirectBack('Admin access updated.', 'success');
    }

    public function grantClientAccess(User $user, string $client): RedirectResponse
    {
        $this->oauthClientService->grantUserAccess($client, $user->id);

        return $this->redirectBack('Access granted.', 'success');
    }

    public function revokeClientAccess(User $user, string $client): RedirectResponse
    {
        $this->oauthClientService->revokeUserAccess($client, $user->id);

        return $this->redirectBack('Access revoked.', 'success');
    }
}
