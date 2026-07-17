<?php

namespace App\Repositories\Interfaces;

use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface extends BaseRepositoryInterface
{
    public function allByProject(Project $project, array $filters = []): LengthAwarePaginator;

    public function findByUuidAndProject(string $uuid, Project $project): ?Model;
}
