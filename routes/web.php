<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Organized by feature/resource following standard Laravel MVC conventions.
|
*/

// --- Public Routes ---
Route::get('/', [HomeController::class, 'index'])->name('home');

// Future public feature routes:
// Route::resource('animals', AnimalController::class)->only(['index', 'show']);
// Route::resource('adoption-requests', AdoptionRequestController::class)->only(['create', 'store']);


// --- Admin Routes ---
Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Future admin management routes:
        // Route::resource('animals', AdminAnimalController::class);
        // Route::resource('adoption-requests', AdminAdoptionRequestController::class);
    });
