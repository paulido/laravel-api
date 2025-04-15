<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TranslationController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::middleware(['language'])->group(function () {

    // Public Routes
    Route::post("register", [AuthController::class, "register"]);
    Route::post("login", [AuthController::class, "login"]);
    Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    // Handle passwork link in email clicked
    Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.reset');
    // Handle when user click link in email
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware('signed')->name('verification.verify');
    // Resent verification email link
    Route::post('/resent-email', [AuthController::class, 'resendVerficationEmail'])->middleware('throttle:6:1');

    Route::resource('users', UserController::class);

    Route::get('/translations',  [TranslationController::class, 'index']);

    Route::get('/currency', function () {
        return response()->json(formatCurrency(5000));
    });

    Route::get("users", [UserController::class, "index"]);
    Route::get('/error', function (Request $request) {
        try {
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

    });
});
