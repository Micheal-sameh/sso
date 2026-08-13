<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\CreateUserDTO;
use App\DTOs\UserDTO;
use App\Enums\UserRole;
use App\Http\Controllers\BaseController;
use App\Http\Requests\CreateUserRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Models\User;
use App\Repositories\Contracts\TokenRepositoryContract;
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
    ) {}

    public function index(Request $request): Response
    {
        $users = $this->userService->paginate(20, $request->string('search')->toString() ?: null, $request->enum('role', UserRole::class));

        return $this->inertia('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only('search', 'role'),
        ]);
    }

    public function create(): Response
    {
        return $this->inertia('Admin/Users/Create');
    }

    public function show(User $user): Response
    {
        return $this->inertia('Admin/Users/Show', [
            'viewedUser' => UserDTO::fromModel($user)->toArray(),
            'tokens' => $this->tokenRepository->listForUser($user->id),
        ]);
    }

    public function store(CreateUserRequest $request): RedirectResponse
    {
        $this->userService->create(CreateUserDTO::fromRequest($request));

        return $this->redirectRoute('admin.users.index', 'User created successfully.');
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
}
