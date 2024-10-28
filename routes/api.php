<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;


Route::middleware(['language'])->group(function () {

    // Public Routes
    Route::post("register", [AuthController::class, "register"]);
    Route::post("login", [AuthController::class, "login"]);
    Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.reset');

    // Route::get('/test', function (Request $request) {
    //     return response()->json(formatCurrency2(5000));
    // });
    Route::get("test", [AuthController::class, "money"]);
    Route::get("users", [AuthController::class, "index"]);
    Route::get('/error', function (Request $request) {
        try {
            // This will throw a DivisionByZeroError
            return response()->json(10 / 0);
        } catch (\Exception $e) {
            Log::error('error Ido: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error has occurred. Please try again later',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    });

    // Authenticated Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get("logout", [AuthController::class, "logout"]);
        Route::post("autorisation", [AuthController::class, "autorisation"]);

        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // Additional authenticated user routes can go here
    });

    // Admin Routes
    Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
        Route::get('/admin/dashboard', function () {
            return response()->json(['message' => 'Welcome to the admin dashboard!']);
        });

        // Other admin routes can go here
    });
});
