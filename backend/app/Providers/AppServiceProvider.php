<?php

namespace App\Providers;

use App\Events\TaskAssigned;
use App\Listeners\TaskAssignedListener;
use App\Models\Project;
use App\Models\Task;
use App\Policies\ProjectPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Policies
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);

        // Gates
        Gate::define('admin-only', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('manage-users', function ($user) {
            return $user->role === 'admin';
        });

        // Event-Listener
        Event::listen(TaskAssigned::class, TaskAssignedListener::class);
    }
}
