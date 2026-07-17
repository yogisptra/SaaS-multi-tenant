<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Admin & Member dapat melihat daftar task milik company-nya.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Admin & Member dapat melihat detail task milik company-nya.
     */
    public function view(User $user, Task $task): bool
    {
        return $user->company_id === $task->company_id;
    }

    /**
     * Hanya Admin yang dapat membuat task.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Admin dapat mengupdate semua task.
     * Member hanya dapat mengupdate task yang di-assign kepadanya.
     */
    public function update(User $user, Task $task): bool
    {
        if ($user->company_id !== $task->company_id) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        // Member hanya boleh update task miliknya sendiri
        return $user->role === 'member' && $task->assigned_to === $user->id;
    }

    /**
     * Hanya Admin yang dapat menghapus task.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->role === 'admin' && $user->company_id === $task->company_id;
    }

    public function restore(User $user, Task $task): bool
    {
        return $user->role === 'admin' && $user->company_id === $task->company_id;
    }
}
