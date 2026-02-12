<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\PostController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Public\ContactMessageController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Admin & Moderator Routes
Route::prefix('admin')->group(function () {

    Route::prefix('categories')->group(function () {

        Route::get('/', [AdminCategoryController::class, 'index']);
        Route::get('/{id}', [AdminCategoryController::class, 'show']);
        Route::post('/', [AdminCategoryController::class, 'store']);
        Route::put('/{id}', [AdminCategoryController::class, 'update']);
        Route::delete('/{id}', [AdminCategoryController::class, 'destroy']);
    });
});

// Public Routes
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{slug}', [PostController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/contact', [ContactMessageController::class, 'store']);
