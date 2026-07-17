<?php

namespace App\Http\Controllers;

use App\DTO\ProjectDTO;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private ProjectService $projectService)
    {
    }

    /**
     * GET /api/v1/projects
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Project::class);

        $projects = $this->projectService->getAllProjects($request->all());

        return $this->successResponse(
            'Projects retrieved successfully',
            ProjectResource::collection($projects)->response()->getData(true)
        );
    }

    /**
     * POST /api/v1/projects
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $this->authorize('create', Project::class);

        $dto = ProjectDTO::fromRequest($request->validated());
        $project = $this->projectService->createProject($dto, auth('api')->id());

        return $this->successResponse(
            'Project created successfully',
            new ProjectResource($project),
            201
        );
    }

    /**
     * GET /api/v1/projects/{uuid}
     */
    public function show(string $uuid): JsonResponse
    {
        $project = $this->projectService->getProjectByUuid($uuid);

        if (!$project) {
            return $this->errorResponse('Project not found', 404);
        }

        $this->authorize('view', $project);

        return $this->successResponse(
            'Project retrieved successfully',
            new ProjectResource($project)
        );
    }

    /**
     * PATCH /api/v1/projects/{uuid}
     */
    public function update(UpdateProjectRequest $request, string $uuid): JsonResponse
    {
        $project = $this->projectService->getProjectByUuid($uuid);

        if (!$project) {
            return $this->errorResponse('Project not found', 404);
        }

        $this->authorize('update', $project);

        $dto = ProjectDTO::fromRequest(array_merge($project->toArray(), $request->validated()));
        $project = $this->projectService->updateProject($project, $dto);

        return $this->successResponse(
            'Project updated successfully',
            new ProjectResource($project->load(['creator', 'tasks']))
        );
    }

    /**
     * DELETE /api/v1/projects/{uuid}
     */
    public function destroy(string $uuid): JsonResponse
    {
        $project = $this->projectService->getProjectByUuid($uuid);

        if (!$project) {
            return $this->errorResponse('Project not found', 404);
        }

        $this->authorize('delete', $project);

        $this->projectService->deleteProject($project);

        return $this->successResponse('Project deleted successfully');
    }
}
