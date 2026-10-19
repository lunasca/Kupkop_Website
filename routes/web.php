<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
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


// --- Authentication Routes (Guest Only) ---
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Logout Route (Authenticated Only)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');


// --- Admin Routes (Authenticated + Admin Only) ---
Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Future admin management routes:
        // Route::resource('animals', AdminAnimalController::class);
        // Route::resource('adoption-requests', AdminAdoptionRequestController::class);
    });
