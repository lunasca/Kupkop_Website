<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
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

// --- Safety Fallback Redirects ---
Route::redirect('/home', '/');
Route::redirect('/dashboard', '/');
Route::redirect('/admin', '/admin/dashboard');

// --- Admin Web Portal Routes ---
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
});

// --- Authenticated Routes (Requires Login) ---
Route::middleware('auth')->group(function () {
    // My Posts & Post Creation / Editing Flow
    Route::get('/my-posts', [PostController::class, 'index'])->name('my-posts');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::put('/posts/{pet}', [PostController::class, 'update'])->name('posts.update');
    Route::patch('/posts/{pet}/adopt', [PostController::class, 'markAsAdopted'])->name('posts.adopt');
    Route::delete('/posts/{pet}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/adoption-requests', [PostController::class, 'storeAdoptionRequest'])->name('adoption-requests.store');
    Route::patch('/requests/{requestRecord}/status', [PostController::class, 'updateRequestStatus'])->name('requests.status');
    Route::delete('/requests/{requestRecord}/cancel', [PostController::class, 'cancelAdoptionRequest'])->name('requests.cancel');
    Route::delete('/requests/{requestRecord}', [PostController::class, 'destroyApplication'])->name('requests.destroy');

    // Messages Tab & API
    Route::get('/messages', [MessagesController::class, 'index'])->name('messages');
    Route::post('/messages', [MessagesController::class, 'send'])->name('messages.send');
    Route::post('/messages/{partner}/read', [MessagesController::class, 'markAsRead'])->name('messages.read');
    Route::get('/messages/{partner}/poll', [MessagesController::class, 'pollMessages'])->name('messages.poll');

    // Profile Edit
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Logout Route
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// --- Authentication Routes (Guest Only) ---
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});
