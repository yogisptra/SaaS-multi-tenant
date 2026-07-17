<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    public function __construct(protected Model $model)
    {
    }

    public function all(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Search
        if (!empty($filters['search']) && !empty($this->searchableFields())) {
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

        // Filter by priority (for tasks)
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($filters['per_page'] ?? 15, 100);

        return $query->paginate($perPage);
    }

    public function findByUuid(string $uuid): ?Model
    {
        return $this->model->newQuery()->where('uuid', $uuid)->first();
    }

    public function create(array $data): Model
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $model->update($data);
        return $model->fresh();
    }

    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    protected function getLikeOperator(): string
    {
        return \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite' ? 'LIKE' : 'ILIKE';
    }

    /**
     * Fields that are searchable via ?search= query parameter.
     * Override in child repositories.
     */
    protected function searchableFields(): array
    {
        return [];
    }
}
