<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkDeleteProjectBlackListRequest;
use App\Http\Requests\StoreProjectBlackListRequest;
use App\Http\Requests\UpdateProjectBlackListRequest;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectBlackListService;
use Illuminate\Http\Request;

class ProjectBlackListController extends Controller
{
    public function __construct(protected ProjectBlackListService $projectBlackListService) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'project_id']);
        $projectBlackLists = $this->projectBlackListService->getAllProjectBlackLists($filters);
        $projects = Project::all();

        return view('project-black-lists.index', compact('projectBlackLists', 'projects', 'filters'));
    }

    public function create(Request $request)
    {
        $projects = Project::all();
        $users = User::all();
        $selectedUserId = $request->get('user_id');

        return view('project-black-lists.create', compact('projects', 'users', 'selectedUserId'));
    }

    public function store(StoreProjectBlackListRequest $request)
    {
        $data = $request->all();
        $data['created_by'] = auth()->id();

        $this->projectBlackListService->createProjectBlackList($data);

        return redirect()->route('project-black-lists.index')->with('success', 'Project Black List created successfully.');
    }

    public function show($id)
    {
        $projectBlackList = $this->projectBlackListService->getProjectBlackListById($id);

        return view('project-black-lists.show', compact('projectBlackList'));
    }

    public function edit($id)
    {
        $projectBlackList = $this->projectBlackListService->getProjectBlackListById($id);
        $projects = Project::all();
        $users = User::all();

        return view('project-black-lists.edit', compact('projectBlackList', 'projects', 'users'));
    }

    public function update(UpdateProjectBlackListRequest $request, $id)
    {
        $projectBlackList = $this->projectBlackListService->getProjectBlackListById($id);
        $this->projectBlackListService->updateProjectBlackList($projectBlackList, $request->validated());

        return redirect()->route('project-black-lists.index')->with('success', 'Project Black List updated successfully.');
    }

    public function destroy($id)
    {
        $projectBlackList = $this->projectBlackListService->getProjectBlackListById($id);
        $this->projectBlackListService->deleteProjectBlackList($projectBlackList);

        return redirect()->route('project-black-lists.index')->with('success', 'Project Black List deleted successfully.');
    }

    public function bulkDelete(BulkDeleteProjectBlackListRequest $request)
    {
        $deleted = $this->projectBlackListService->bulkDelete($request->validated()['ids']);

        return response()->json(['success' => (bool) $deleted, 'deleted' => $deleted]);
    }
}
