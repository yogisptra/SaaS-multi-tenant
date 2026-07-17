<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class ProjectRestoreController extends Controller
{
    use ApiResponseTrait;

    public function __invoke(string $uuid): JsonResponse
    {
        $project = Project::withTrashed()->where('uuid', $uuid)->firstOrFail();
        
        $this->authorize('restore', $project);
        
        $project->restore();

        return $this->successResponse('Project restored successfully', $project);
    }
}
