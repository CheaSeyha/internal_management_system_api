<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\CardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::controller(AuthController::class)->group(function () {
    // Public routes
    // These routes do not require authentication
    // They are used for user registration and login
    // The AuthController handles the logic for these actions
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/refresh-token', 'refreshToken');
});

// Protected routes
// These routes require authentication
// They are used for actions that require a logged-in user
// The AuthController handles the logic for these actions
Route::middleware('auth:api')->group(function () {
    // Add protected routes here, e.g.:
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::resource('cards', CardController::class);
});
