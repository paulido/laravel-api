<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['language'])->group(function () {

    // Public Routes
    Route::post("register", [AuthController::class, "register"]);
    Route::post("login", [AuthController::class, "login"]);
    Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.reset');
    
    Route::get('/test', function (Request $request) {
        return response()->json(__('messages.login_successful'));
    });

    // Authenticated Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get("logout", [AuthController::class, "logout"]);
        
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // Additional authenticated user routes can go here
    });

    // Admin Routes
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/admin/dashboard', function () {
            return response()->json(['message' => 'Welcome to the admin dashboard!']);
        });

        // Other admin routes can go here
    });
});
