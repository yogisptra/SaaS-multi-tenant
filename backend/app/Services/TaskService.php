<?php

namespace App\Services;

use App\DTO\TaskDTO;
use App\Models\Project;
use App\Models\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService
{
    public function __construct(private TaskRepositoryInterface $taskRepository)
    {
    }

    public function getAllTasksByProject(Project $project, array $filters = []): LengthAwarePaginator
    {
        return $this->taskRepository->allByProject($project, $filters);
    }

    public function getTaskByUuid(string $uuid, Project $project): ?Task
    {
        return $this->taskRepository->findByUuidAndProject($uuid, $project);
    }

    public function createTask(TaskDTO $dto, Project $project): Task
    {
        $data = array_merge($dto->toArray(), [
            'project_id' => $project->id,
        ]);

        $task = $this->taskRepository->create($data);

        return $task->load(['assignee', 'project']);
    }

    public function updateTask(Task $task, TaskDTO $dto): Task
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($task, $dto) {
            // Pessimistic locking to prevent race conditions
            $lockedTask = Task::lockForUpdate()->find($task->id);

            $oldAssignedTo = $lockedTask->assigned_to;
            $data = $dto->toArray();

            $updatedTask = $this->taskRepository->update($lockedTask, $data);
            $newAssignedTo = $updatedTask->assigned_to;

            // Jika assigned_to berubah, dispatch event
            if ($oldAssignedTo !== $newAssignedTo && $newAssignedTo !== null) {
                event(new \App\Events\TaskAssigned($updatedTask));
            }

            return $updatedTask->load(['assignee', 'project']);
        });
    }

    public function deleteTask(Task $task): bool
    {
        return $this->taskRepository->delete($task);
    }
}
