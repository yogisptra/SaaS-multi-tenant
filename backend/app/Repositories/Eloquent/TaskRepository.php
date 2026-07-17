<?php

namespace App\Repositories\Eloquent;

use App\Models\Project;
use App\Models\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskRepository extends BaseRepository implements TaskRepositoryInterface
{
    public function __construct(Task $model)
    {
        parent::__construct($model);
    }

    public function allByProject(Project $project, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->where('project_id', $project->id)
            ->with(['assignee']);

        // Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                foreach ($this->searchableFields() as $field) {
                    $q->orWhere($field, 'ILIKE', "%{$search}%");
                }
            });
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by priority
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        // Filter by assigned_to
        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = min($filters['per_page'] ?? 15, 100);

        return $query->paginate($perPage);
    }

    public function findByUuidAndProject(string $uuid, Project $project): ?Model
    {
        return $this->model->newQuery()
            ->where('uuid', $uuid)
            ->where('project_id', $project->id)
            ->with(['assignee', 'project'])
            ->first();
    }

    protected function searchableFields(): array
    {
        return ['title', 'description'];
    }
}
