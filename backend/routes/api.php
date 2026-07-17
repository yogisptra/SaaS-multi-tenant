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
        // Projects
        Route::apiResource('projects', ProjectController::class)
            ->parameters(['projects' => 'uuid']);

        // Tasks (nested under projects)
        Route::apiResource('projects.tasks', TaskController::class)
            ->parameters(['projects' => 'projectUuid', 'tasks' => 'taskUuid']);
    });
});
