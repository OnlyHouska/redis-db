<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('app'));
Route::get('/tasks', fn() => view('app'));
Route::get('/login', fn() => view('app'));
Route::get('/register', fn() => view('app'));
