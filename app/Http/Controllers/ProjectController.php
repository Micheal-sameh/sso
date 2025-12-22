<?php

namespace App\Http\Controllers;

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

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
        ]);

        $project = $this->projectService->createProject($request->only('name', 'url'));

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

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
        ]);

        $project = $this->projectService->getProjectById($id);
        $this->projectService->updateProject($project, $request->only('name', 'url'));

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy($id)
    {
        $project = $this->projectService->getProjectById($id);
        $this->projectService->deleteProject($project);

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }
}
