<?php

namespace App\Repositories;

use App\Models\Project;

class ProjectRepository
{
    public function __construct(protected Project $model) {}

    public function getAllProjects($filters = [])
    {
        $query = $this->model->query()->with('createdBy')->select('id', 'name', 'created_at', 'created_by');

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('secret_key', 'like', "%{$search}%");
        }

        return $query->paginate(10);
    }

    public function getProjectById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function createProject(array $data)
    {
        return $this->model->create($data);
    }

    public function updateProject($project, array $data)
    {
        $project->update($data);

        return $project;
    }

    public function deleteProject($project)
    {
        return $project->delete();
    }
}
