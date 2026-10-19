<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Organized by feature/resource following standard Laravel MVC conventions.
|
*/

// Home Route
Route::get('/', [HomeController::class, 'index'])->name('home');

// Future feature resource routes will be added here cleanly:
// Route::resource('animals', AnimalController::class);
// Route::resource('adoption-requests', AdoptionRequestController::class)->only(['store', 'update']);
// Route::resource('messages', MessageController::class);
