<?php

namespace App\Http\Controllers;

use App\DTO\TaskDTO;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\ProjectService;
use App\Services\TaskService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private TaskService $taskService,
        private ProjectService $projectService,
    ) {
    }

    /**
     * GET /api/v1/projects/{projectUuid}/tasks
     */
    public function index(Request $request, string $projectUuid): JsonResponse
    {
        $project = $this->projectService->getProjectByUuid($projectUuid);

        if (!$project) {
            return $this->errorResponse('Project not found', 404);
        }

        $this->authorize('viewAny', Task::class);

        $tasks = $this->taskService->getAllTasksByProject($project, $request->all());

        return $this->successResponse(
            'Tasks retrieved successfully',
            TaskResource::collection($tasks)->response()->getData(true)
        );
    }

    /**
     * POST /api/v1/projects/{projectUuid}/tasks
     */
    public function store(StoreTaskRequest $request, string $projectUuid): JsonResponse
    {
        $project = $this->projectService->getProjectByUuid($projectUuid);

        if (!$project) {
            return $this->errorResponse('Project not found', 404);
        }

        $this->authorize('create', Task::class);

        $dto = TaskDTO::fromRequest($request->validated());
        $task = $this->taskService->createTask($dto, $project);

        return $this->successResponse(
            'Task created successfully',
            new TaskResource($task),
            201
        );
    }

    /**
     * GET /api/v1/projects/{projectUuid}/tasks/{taskUuid}
     */
    public function show(string $projectUuid, string $taskUuid): JsonResponse
    {
        $project = $this->projectService->getProjectByUuid($projectUuid);

        if (!$project) {
            return $this->errorResponse('Project not found', 404);
        }

        $task = $this->taskService->getTaskByUuid($taskUuid, $project);

        if (!$task) {
            return $this->errorResponse('Task not found', 404);
        }

        $this->authorize('view', $task);

        return $this->successResponse(
            'Task retrieved successfully',
            new TaskResource($task)
        );
    }

    /**
     * PATCH /api/v1/projects/{projectUuid}/tasks/{taskUuid}
     */
    public function update(UpdateTaskRequest $request, string $projectUuid, string $taskUuid): JsonResponse
    {
        $project = $this->projectService->getProjectByUuid($projectUuid);

        if (!$project) {
            return $this->errorResponse('Project not found', 404);
        }

        $task = $this->taskService->getTaskByUuid($taskUuid, $project);

        if (!$task) {
            return $this->errorResponse('Task not found', 404);
        }

        $this->authorize('update', $task);

        $dto = TaskDTO::fromRequest(array_merge($task->toArray(), $request->validated()));
        $task = $this->taskService->updateTask($task, $dto);

        return $this->successResponse(
            'Task updated successfully',
            new TaskResource($task)
        );
    }

    /**
     * DELETE /api/v1/projects/{projectUuid}/tasks/{taskUuid}
     */
    public function destroy(string $projectUuid, string $taskUuid): JsonResponse
    {
        $project = $this->projectService->getProjectByUuid($projectUuid);

        if (!$project) {
            return $this->errorResponse('Project not found', 404);
        }

        $task = $this->taskService->getTaskByUuid($taskUuid, $project);

        if (!$task) {
            return $this->errorResponse('Task not found', 404);
        }

        $this->authorize('delete', $task);

        $this->taskService->deleteTask($task);

        return $this->successResponse('Task deleted successfully');
    }
}
