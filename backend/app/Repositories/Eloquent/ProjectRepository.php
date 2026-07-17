<?php

namespace App\Repositories\Eloquent;

use App\Models\Project;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectRepository extends BaseRepository implements ProjectRepositoryInterface
{
    public function __construct(Project $model)
    {
        parent::__construct($model);
    }

    public function allWithRelations(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['creator', 'tasks']);

        // Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $operator = $this->getLikeOperator();
            $query->where(function ($q) use ($search, $operator) {
                foreach ($this->searchableFields() as $field) {
                    $q->orWhere($field, $operator, "%{$search}%");
                }
            });
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = min($filters['per_page'] ?? 15, 100);

        return $query->paginate($perPage);
    }

    protected function searchableFields(): array
    {
        return ['name', 'description'];
    }
}
