<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class TaskRestoreController extends Controller
{
    use ApiResponseTrait;

    public function __invoke(string $projectUuid, string $taskUuid): JsonResponse
    {
        $project = Project::where('uuid', $projectUuid)->firstOrFail();
        $task = Task::withTrashed()->where('uuid', $taskUuid)->where('project_id', $project->id)->firstOrFail();

        $this->authorize('restore', $task);

        $task->restore();

        return $this->successResponse('Task restored successfully', $task);
    }
}
