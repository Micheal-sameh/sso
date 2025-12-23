<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(protected ProjectService $projectService) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search']);
        $projects = $this->projectService->getAllProjects($filters);

        return view('projects.index', compact('projects', 'filters'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(StoreProjectRequest $request)
    {
        $data = $request->validated();

        $project = $this->projectService->createProject($data);

        return redirect()->route('projects.index')
            ->with('success', 'Project created successfully.')
            ->with('secret_key', $project->secret_key);
    }

    public function show($id)
    {
        $project = $this->projectService->getProjectById($id);

        return view('projects.show', compact('project'));
    }

    public function edit($id)
    {
        $project = $this->projectService->getProjectById($id);

        return view('projects.edit', compact('project'));
    }

    public function update(UpdateProjectRequest $request, $id)
    {
        $data = $request->validated();

        $project = $this->projectService->getProjectById($id);
        $this->projectService->updateProject($project, $data);

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy($id)
    {
        $project = $this->projectService->getProjectById($id);
        $this->projectService->deleteProject($project);

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }
}
