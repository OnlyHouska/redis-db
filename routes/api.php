<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

Route::get('/tasks', [TaskController::class, 'index']);
Route::post('/tasks/create', [TaskController::class, 'store']);
Route::put('/tasks/{id}/toggle', [TaskController::class, 'toggle']);
Route::delete('/tasks/{id}/delete', [TaskController::class, 'destroy']);
