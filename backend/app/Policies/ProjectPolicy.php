<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Admin & Member dapat melihat daftar project milik company-nya.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Admin & Member dapat melihat detail project milik company-nya.
     */
    public function view(User $user, Project $project): bool
    {
        return $user->company_id === $project->company_id;
    }

    /**
     * Hanya Admin yang dapat membuat project.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya Admin yang dapat mengupdate project.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->role === 'admin' && $user->company_id === $project->company_id;
    }

    /**
     * Hanya Admin yang dapat menghapus project.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->role === 'admin' && $user->company_id === $project->company_id;
    }
}
