<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FamilyRole;
use App\Http\Controllers\BaseController;
use App\Http\Requests\AssignFamilyMemberRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Models\Family;
use App\Models\User;
use App\Services\FamilyService;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class FamilyController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected FamilyService $familyService,
    ) {}

    public function index(): Response
    {
        $families = $this->familyService->listAll();

        return $this->inertia('Admin/Families/Index', [
            'families' => $families,
            'totalFamilies' => $families->count(),
        ]);
    }

    public function show(Family $family): Response
    {
        $family->loadCount('users')->load(['father', 'mother', 'children']);

        $users = User::query()
            ->with('family')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'membership_code', 'family_id', 'family_role'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'membership_code' => $user->membership_code,
                'current_role' => $user->family_id === $family->id ? $user->family_role?->value : null,
                'current_family_name' => $user->family_id !== null && $user->family_id !== $family->id
                    ? $user->family->display_name
                    : null,
            ])
            ->values();

        return $this->inertia('Admin/Families/Show', [
            'family' => $family,
            'users' => $users,
        ]);
    }

    public function store(): RedirectResponse
    {
        $family = $this->familyService->create();

        return $this->redirectRoute('admin.families.show', __('messages.family_created'), params: ['family' => $family->id]);
    }

    public function destroy(Family $family): RedirectResponse
    {
        $this->familyService->delete($family);

        return $this->redirectRoute('admin.families.index', __('messages.family_deleted'));
    }

    public function addMember(Family $family, AssignFamilyMemberRequest $request): RedirectResponse
    {
        $user = User::findOrFail($request->integer('user_id'));

        $this->familyService->addMember($family, $user, FamilyRole::from($request->string('role')->toString()));

        return $this->redirectBack(__('messages.family_member_added'), 'success');
    }

    public function removeMember(Family $family, User $user): RedirectResponse
    {
        $this->familyService->removeMember($user);

        return $this->redirectBack(__('messages.family_member_removed'), 'success');
    }

    public function requireDataUpdateAll(): RedirectResponse
    {
        $count = $this->familyService->requireDataUpdateForAllFamilies();

        return $this->redirectBack(__('messages.family_require_data_update_all', ['count' => $count]), 'success');
    }

    public function requireDataUpdateForFamily(Family $family): RedirectResponse
    {
        $count = $this->familyService->requireDataUpdateForFamily($family);

        return $this->redirectBack(__('messages.family_require_data_update_one', ['count' => $count]), 'success');
    }
}
