<?php

use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/tasks', [TaskController::class, 'index']);
Route::post('/tasks/create', [TaskController::class, 'store']);
Route::put('/tasks/{id}/toggle', [TaskController::class, 'toggle']);
Route::delete('/tasks/{id}/delete', [TaskController::class, 'destroy']);
