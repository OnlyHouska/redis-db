<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'hello';
});

Route::get('/tasks', function () {
    return view('tasks');
});
