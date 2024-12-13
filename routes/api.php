<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RestaurantController;

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

    // USER
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    // RESTO
    Route::apiResource('restaurants', RestaurantController::class);

    // TEST
    Route::get('/test', function () {
        return response()->json(['status' => 'success']);
    });

    //! Routes ADMIN
    require base_path('routes/admin.php');
});




//Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//    return $request->user();
//});

