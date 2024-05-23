<?php

namespace App\Interfaces;

interface ProjectRepositoryInterface
{
    public function getAllProjects();
    public function getProjectById($projectId);
    public function deleteProject($projectId);
    public function createProject(array $projectDetails);
    public function updateProject($projectId, array $newDetails);
    public function getPublicProjects();
}
