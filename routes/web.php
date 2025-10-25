<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Filament routes
Route::middleware(['auth'])->group(function () {
    Route::get('/admin', function () {
        return redirect('/admin/dashboard');
    });
});
