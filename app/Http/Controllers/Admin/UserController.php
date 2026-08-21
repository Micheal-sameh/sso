<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\CreateUserDTO;
use App\DTOs\UserDTO;
use App\Enums\UserRole;
use App\Http\Controllers\BaseController;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Models\ClientMeta;
use App\Models\Group;
use App\Models\User;
use App\Repositories\Contracts\TokenRepositoryContract;
use App\Services\GroupService;
use App\Services\MfaService;
use App\Services\OAuthClientService;
use App\Services\PasswordResetService;
use App\Services\StageService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
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
        protected PasswordResetService $passwordResetService,
        protected GroupService $groupService,
        protected MfaService $mfaService,
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

        $user->loadMissing('groups');
        $groups = $this->groupService->listAll()
            ->map(fn (Group $group) => [
                'id' => $group->id,
                'name' => $group->name,
                'is_member' => $user->groups->contains('id', $group->id),
            ])
            ->values();

        return $this->inertia('Admin/Users/Show', [
            'viewedUser' => UserDTO::fromModel($user)->toArray(),
            'tokens' => $this->tokenRepository->listForUser($user->id)->map(fn ($token) => $token->toDisplayArray()),
            'specificClients' => $specificClients,
            'groups' => $groups,
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

    public function toggleRequiresDataUpdate(User $user): RedirectResponse
    {
        $this->userService->toggleRequiresDataUpdate($user);

        return $this->redirectBack('User data-update requirement updated.', 'success');
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

    public function attachGroup(User $user, Group $group): RedirectResponse
    {
        $this->userService->addToGroup($user, $group->id);

        return $this->redirectBack('Added to group.', 'success');
    }

    public function detachGroup(User $user, Group $group): RedirectResponse
    {
        $this->userService->removeFromGroup($user, $group->id);

        return $this->redirectBack('Removed from group.', 'success');
    }

    public function generateResetLink(User $user): JsonResponse
    {
        return response()->json(['link' => $this->passwordResetService->generateResetLink($user)]);
    }

    public function generateAvatarUploadLink(User $user): JsonResponse
    {
        return response()->json(['link' => $this->userService->generateAvatarUploadLink($user)]);
    }

    public function resetMfa(User $user): RedirectResponse
    {
        $this->mfaService->resetMfa($user);

        return $this->redirectBack("2FA reset for {$user->name}. They'll need to set it up again.", 'success');
    }
}
