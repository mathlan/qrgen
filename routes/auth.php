<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

//* PUBLIC (no auth)
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/forgot-password', [PasswordResetController::class, 'store']);
Route::post('/reset-password', [NewPasswordController::class, 'store']);
Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, 'store']);

//* PRIVATE (JWT auth)
Route::middleware('jwt.auth')->group(function () {
    Route::post('/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->name('verification.send');
    // AUTH
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
});
