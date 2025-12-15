<?php

use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

// Auth / Users
Route::post('/auth/register', [UserController::class, 'register']);
Route::post('/auth/login', [UserController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected routes (JWT)
|--------------------------------------------------------------------------
*/

Route::middleware('auth.jwt')->group(function () {
    // Auth
    Route::get('/auth/me', [UserController::class, 'me']);
    Route::post('/auth/logout', [UserController::class, 'logout']);

    // Tasks
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks/create', [TaskController::class, 'store']);
    Route::put('/tasks/{id}/toggle', [TaskController::class, 'toggle']);
    Route::delete('/tasks/{id}/delete', [TaskController::class, 'destroy']);
});
