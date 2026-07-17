<?php

namespace App\Services;

use App\DTO\ProjectDTO;
use App\Models\Project;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function __construct(private ProjectRepositoryInterface $projectRepository)
    {
    }

    public function getAllProjects(array $filters = []): LengthAwarePaginator
    {
        return $this->projectRepository->allWithRelations($filters);
    }

    public function getProjectByUuid(string $uuid): ?Project
    {
        $project = $this->projectRepository->findByUuid($uuid);

        if (!$project) {
            return null;
        }

        return $project->load(['creator', 'tasks']);
    }

    public function createProject(ProjectDTO $dto, int $userId): Project
    {
        $data = array_merge($dto->toArray(), [
            'created_by' => $userId,
        ]);

        $project = $this->projectRepository->create($data);

        return $project->load(['creator', 'tasks']);
    }

    public function updateProject(Project $project, ProjectDTO $dto): Project
    {
        return $this->projectRepository->update($project, $dto->toArray());
    }

    public function deleteProject(Project $project): bool
    {
        return $this->projectRepository->delete($project);
    }
}
