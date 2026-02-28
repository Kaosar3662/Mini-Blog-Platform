<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\PostController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminContactMessageController;
use App\Http\Controllers\Public\ContactMessageController;
use App\Http\Controllers\Public\AuthController;
use App\Http\Controllers\Admin\CreateModerator;
use App\Http\Controllers\Public\PasswordController;
use App\Http\Controllers\Public\EmailVerificationController;
use App\Http\Controllers\Blogger\PostController as BloggerPostController;
use App\Http\Controllers\Admin\AdminPostController;
use App\Http\Controllers\Admin\UserController;

// Public Routes
Route::get('/posts', [PostController::class, 'index']); 
Route::get('/posts/{slug}', [PostController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/contact', [ContactMessageController::class, 'store']);

// Public Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Email verification (blogger only) using token
    Route::post('/email/verify', [EmailVerificationController::class, 'verify']);
    Route::post('/email/resend', [EmailVerificationController::class, 'resend']);

    // Forgot password using token
    Route::post('/password/forgot', [PasswordController::class, 'sendResetLink']);
    Route::post('/password/reset', [PasswordController::class, 'reset']);
});

// Admin & Moderator Routes
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin,moderator'])->group(function () {

    // Admin-only: create moderator
    Route::post('/create-moderator', [CreateModerator::class, 'store']);
    Route::post('/user/{email}', [UserController::class, 'update']);
    Route::get('/users', [UserController::class, 'index']);

    // Category CRUD
    Route::prefix('categories')->group(function () {
        Route::get('/', [AdminCategoryController::class, 'index']);
        Route::get('/{id}', [AdminCategoryController::class, 'show']);
        Route::post('/', [AdminCategoryController::class, 'store']);
        Route::put('/{id}', [AdminCategoryController::class, 'update']);
        Route::delete('/{id}', [AdminCategoryController::class, 'destroy']);
    });

    // Post management
    Route::get('/posts', [AdminPostController::class, 'index']); 
    Route::get('/posts/{slug}', [AdminPostController::class, 'show']); 
    Route::put('/posts/{slug}/approve', [AdminPostController::class, 'approve']);
    Route::put('/posts/{slug}/reject', [AdminPostController::class, 'reject']);

    // Contact Messages
    Route::get('/messages', [AdminContactMessageController::class, 'index']);
    Route::get('/messages/{id}', [AdminContactMessageController::class, 'show']);
    Route::delete('/messages/{id}', [AdminContactMessageController::class, 'destroy']);
});

// Authenticated blogger Routes
Route::prefix('blogger')->middleware(['auth:sanctum' , 'role:admin,moderator,blogger'])->group(function () {
    Route::get('/posts', [BloggerPostController::class, 'index']);
    Route::get('/posts/{slug}', [BloggerPostController::class, 'show']);
    Route::post('/posts', [BloggerPostController::class, 'store']);
    Route::put('/posts/{slug}', [BloggerPostController::class, 'update']);
    Route::delete('/posts/{slug}', [BloggerPostController::class, 'destroy']);
});

// Authenticated user info
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
