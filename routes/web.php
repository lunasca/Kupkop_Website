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
    Route::get('/posts', [\App\Http\Controllers\Admin\PostController::class, 'index'])->name('posts');
    Route::patch('/posts/{pet}/verify', [\App\Http\Controllers\Admin\PostController::class, 'verify'])->name('posts.verify');
    Route::patch('/posts/{pet}/unverify', [\App\Http\Controllers\Admin\PostController::class, 'unverify'])->name('posts.unverify');
    Route::patch('/posts/{pet}/archive', [\App\Http\Controllers\Admin\PostController::class, 'archive'])->name('posts.archive');
    Route::patch('/posts/{pet}/restore', [\App\Http\Controllers\Admin\PostController::class, 'restore'])->name('posts.restore');
    Route::delete('/posts/{pet}', [\App\Http\Controllers\Admin\PostController::class, 'destroy'])->name('posts.destroy');

    // --- Accounts Management ---
    Route::get('/accounts', [\App\Http\Controllers\Admin\AccountController::class, 'index'])->name('accounts');
    Route::patch('/accounts/{user}/block', [\App\Http\Controllers\Admin\AccountController::class, 'block'])->name('accounts.block');
    Route::patch('/accounts/{user}/unblock', [\App\Http\Controllers\Admin\AccountController::class, 'unblock'])->name('accounts.unblock');
    Route::patch('/accounts/{user}/archive', [\App\Http\Controllers\Admin\AccountController::class, 'archive'])->name('accounts.archive');
    Route::patch('/accounts/{user}/restore', [\App\Http\Controllers\Admin\AccountController::class, 'restore'])->name('accounts.restore');
    Route::delete('/accounts/{user}', [\App\Http\Controllers\Admin\AccountController::class, 'destroy'])->name('accounts.destroy');
});

// --- Authenticated Routes (Requires Login) ---
Route::middleware('auth')->group(function () {
    // User-Only Routes (Restricted for Admin accounts)
    Route::middleware('not.admin')->group(function () {
        // My Posts Page & Create Post Alias
        Route::get('/my-posts', [PostController::class, 'index'])->name('my-posts');
        Route::redirect('/create-post', '/my-posts');

        // Messages Tab & API
        Route::get('/messages', [MessagesController::class, 'index'])->name('messages');
        Route::post('/messages', [MessagesController::class, 'send'])->name('messages.send');
        // Global unread count (must be declared BEFORE {partner} wildcard routes)
        Route::get('/messages/unread', [MessagesController::class, 'unreadCount'])->name('messages.unread');
        Route::post('/messages/{partner}/read', [MessagesController::class, 'markAsRead'])->name('messages.read');
        Route::get('/messages/{partner}/poll', [MessagesController::class, 'pollMessages'])->name('messages.poll');
        Route::patch('/messages/{message}/edit', [MessagesController::class, 'editMessage'])->name('messages.edit');
        Route::delete('/messages/{message}', [MessagesController::class, 'deleteMessage'])->name('messages.delete');
        Route::delete('/messages/{partner}/conversation', [MessagesController::class, 'deleteConversation'])->name('messages.conversation.delete');
    });

    // Pet Post CRUD & Adoption Actions
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::put('/posts/{pet}', [PostController::class, 'update'])->name('posts.update');
    Route::patch('/posts/{pet}/adopt', [PostController::class, 'markAsAdopted'])->name('posts.adopt');
    Route::patch('/posts/{pet}/archive', [PostController::class, 'archive'])->name('posts.archive');
    Route::patch('/posts/{pet}/restore', [PostController::class, 'restore'])->name('posts.restore');
    Route::delete('/posts/{pet}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/adoption-requests', [PostController::class, 'storeAdoptionRequest'])->name('adoption-requests.store');
    Route::patch('/requests/{requestRecord}/status', [PostController::class, 'updateRequestStatus'])->name('requests.status');
    Route::delete('/requests/{requestRecord}/cancel', [PostController::class, 'cancelAdoptionRequest'])->name('requests.cancel');
    Route::delete('/requests/{requestRecord}', [PostController::class, 'destroyApplication'])->name('requests.destroy');

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
