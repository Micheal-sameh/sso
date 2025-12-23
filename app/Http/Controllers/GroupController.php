<?php

namespace App\Http\Controllers;

use App\Events\GroupCreated;
use App\Http\Requests\AddGroupMemberRequest;
use App\Http\Requests\ManageGroupMembersRequest;
use App\Http\Requests\RemoveGroupMemberRequest;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\User;
use App\Services\GroupService;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function __construct(protected GroupService $groupService) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search']);
        $groups = $this->groupService->getAllGroups($filters);

        return view('groups.index', compact('groups', 'filters'));
    }

    public function create()
    {
        return view('groups.create');
    }

    public function store(StoreGroupRequest $request)
    {
        $data = $request->validated();

        $group = $this->groupService->createGroup($data);

        event(new GroupCreated($group));

        return redirect()->route('groups.index')->with('success', 'Group created successfully.');
    }

    public function show($id)
    {
        $group = $this->groupService->getGroupById($id);

        return view('groups.show', compact('group'));
    }

    public function edit($id)
    {
        $group = $this->groupService->getGroupById($id);

        return view('groups.edit', compact('group'));
    }

    public function update(UpdateGroupRequest $request, $id)
    {
        $data = $request->validated();

        $group = $this->groupService->getGroupById($id);
        $this->groupService->updateGroup($group, $data);

        return redirect()->route('groups.index')->with('success', 'Group updated successfully.');
    }

    public function destroy($id)
    {
        $group = $this->groupService->getGroupById($id);
        $this->groupService->deleteGroup($group);

        return redirect()->route('groups.index')->with('success', 'Group deleted successfully.');
    }

    public function addMember(AddGroupMemberRequest $request, $id)
    {
        $group = $this->groupService->getGroupById($id);

        $added = $this->groupService->addMember($group, $request->input('user_id'));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $added ? 'Member added.' : 'Member already in group.']);
        }

        return redirect()->route('groups.show', $group->id)->with('success', 'Member added successfully.');
    }

    public function removeMember(RemoveGroupMemberRequest $request, $id)
    {
        $group = $this->groupService->getGroupById($id);

        $removed = $this->groupService->removeMember($group, $request->input('user_id'));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => $removed, 'message' => $removed ? 'Member removed.' : 'Member not found in group.']);
        }

        return redirect()->route('groups.show', $group->id)->with('success', 'Member removed successfully.');
    }

    public function manageMembers(ManageGroupMembersRequest $request, $id)
    {
        $group = $this->groupService->getGroupById($id);

        $this->groupService->syncMembers($group, $request->input('user_ids', []));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Group members updated successfully.']);
        }

        return redirect()->route('groups.show', $group->id)->with('success', 'Group members updated successfully.');
    }

    public function users(Request $request, $id)
    {
        $q = $request->input('q');
        $perPage = (int) $request->input('per_page', 20);

        $group = $this->groupService->getGroupById($id);
        $memberIds = $group->users->pluck('id')->toArray();

        $query = User::query();

        if ($q) {
            $query->where(function ($inner) use ($q) {
                $inner->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('membership_code', 'like', "%{$q}%");
            });
        }

        $users = $query->select('id', 'name', 'email', 'membership_code')
            ->orderBy('name')
            ->paginate($perPage)
            ->appends($request->only('q', 'per_page'));

        $users->getCollection()->transform(function ($user) use ($memberIds) {
            $user->is_member = in_array($user->id, $memberIds);

            return $user;
        });

        return response()->json($users);
    }
}
