<?php

namespace App\Repositories;

use App\Interfaces\ProjectRepositoryInterface;
use App\Models\Project;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function getAllProjects()
    {
        return Project::all();
    }

    public function getProjectById($projectId)
    {
        return Project::findOrFail($projectId);
    }

    public function deleteProject($projectId)
    {
        Project::destroy($projectId);
    }

    public function createProject(array $projectDetails)
    {
        return Project::create($projectDetails);
    }

    public function updateProject($projectId, array $newDetails)
    {
        return Project::whereId($projectId)->update($newDetails);
    }

    public function getPublicProjects()
    {
        return Project::where('visibility', 'public')->get();
    }
}
