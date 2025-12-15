<?php

namespace App\Services;

use App\Repositories\ProjectRepository;

class ProjectService
{
    public function __construct(protected ProjectRepository $projectRepository) {}

    public function getAllProjects($filters = [])
    {
        return $this->projectRepository->getAllProjects($filters);
    }

    public function getProjectById($id)
    {
        return $this->projectRepository->getProjectById($id);
    }

    public function createProject(array $data)
    {
        if (! isset($data['created_by'])) {
            $data['created_by'] = auth()->id();
        }

        return $this->projectRepository->createProject($data);
    }

    public function updateProject($project, array $data)
    {
        return $this->projectRepository->updateProject($project, $data);
    }

    public function deleteProject($project)
    {
        return $this->projectRepository->deleteProject($project);
    }
}
