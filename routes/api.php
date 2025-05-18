<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
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
});


//Comment controller