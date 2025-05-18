<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\RecipeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Global middleware group

// Public routes with rate limiting
Route::middleware(['guest'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::delete('/logout', [AuthController::class, 'logout']);

    // User profile routes
    Route::get('/user/profile-image', [UserController::class, 'getProfileImage']);
    Route::put('/user/profile-image', [UserController::class, 'updateProfileImage']);

    // Comment routes that require authentication
    Route::post('/comments', [CommentController::class, 'store']);
    Route::put('/comments/{id}', [CommentController::class, 'update']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);


    Route::get('/user/recipes', [RecipeController::class, 'getAllRecipe']);
});

// Public comment routes
Route::get('/recipes/{recipeId}/comments', [CommentController::class, 'getAllCommentsInRecipe']);


// Admin routes
Route::middleware(['auth:sanctum', 'IsAdmin'])->group(function () {
    Route::post('/recipes', [RecipeController::class, 'store']);
    Route::get('/recipes', [RecipeController::class, 'index']);
    Route::put('/recipes/{id}', [RecipeController::class, 'update']);
    Route::delete('/recipes/{id}', [RecipeController::class, 'destroy']);
});
