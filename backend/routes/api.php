<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public auth routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        // Protected auth routes
        Route::middleware('auth:api')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::get('profile', [AuthController::class, 'profile']);
        });
    });

    // Tenant-scoped routes (auth + tenant middleware)
    Route::middleware(['auth:api', 'tenant'])->group(function () {
        // Users
        Route::apiResource('users', \App\Http\Controllers\UserController::class)
            ->parameters(['users' => 'user:uuid']);
            
        // Projects
        Route::patch('projects/{project}/restore', \App\Http\Controllers\ProjectRestoreController::class);
        Route::apiResource('projects', ProjectController::class)
            ->parameters(['projects' => 'uuid']);

        // Tasks (nested under projects)
        Route::patch('projects/{project}/tasks/{task}/restore', \App\Http\Controllers\TaskRestoreController::class);
        Route::apiResource('projects.tasks', TaskController::class)
            ->parameters(['projects' => 'projectUuid', 'tasks' => 'taskUuid']);

        // Notifications
        Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
        Route::patch('notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
        Route::patch('notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);

        // Activity Logs
        Route::get('activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index']);
    });
});
