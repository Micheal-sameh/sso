<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\GroupDTO;
use App\Http\Controllers\BaseController;
use App\Http\Requests\GroupRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Models\Group;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Services\GroupService;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class GroupController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected GroupService $groupService,
        protected UserRepositoryContract $userRepository,
    ) {}

    public function index(): Response
    {
        return $this->inertia('Admin/Groups/Index', [
            'groups' => $this->groupService->listAll(),
        ]);
    }

    public function show(Group $group): Response
    {
        $group->loadCount('users')->load('users:id,name,email,membership_code');

        $memberIds = $group->users->pluck('id');

        $users = $this->userRepository->allLite()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'membership_code' => $user->membership_code,
                'is_member' => $memberIds->contains($user->id),
            ])
            ->values();

        return $this->inertia('Admin/Groups/Show', [
            'group' => $group,
            'users' => $users,
        ]);
    }

    public function store(GroupRequest $request): RedirectResponse
    {
        $this->groupService->create(GroupDTO::fromRequest($request));

        return $this->redirectRoute('admin.groups.index', 'Group created successfully.');
    }

    public function update(Group $group, GroupRequest $request): RedirectResponse
    {
        $this->groupService->update($group, GroupDTO::fromRequest($request));

        return $this->redirectRoute('admin.groups.index', 'Group updated successfully.');
    }

    public function destroy(Group $group): RedirectResponse
    {
        $this->groupService->delete($group);

        return $this->redirectBack('Group deleted.', 'success');
    }
}
