<?php

namespace App\Http\Controllers;

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

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'required|string|max:10',
        ]);

        $this->groupService->createGroup($request->only('name', 'abbreviation'));

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

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'required|string|max:10',
        ]);

        $group = $this->groupService->getGroupById($id);
        $this->groupService->updateGroup($group, $request->only('name', 'abbreviation'));

        return redirect()->route('groups.index')->with('success', 'Group updated successfully.');
    }

    public function destroy($id)
    {
        $group = $this->groupService->getGroupById($id);
        $this->groupService->deleteGroup($group);

        return redirect()->route('groups.index')->with('success', 'Group deleted successfully.');
    }
}
