<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\IngredientController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RestaurantController;

// AUTH
require base_path('routes/auth.php');

//* PUBLIC (no auth)

//* PRIVATE (JWT auth)
Route::middleware('jwt.auth')->group(function () {

    // USER
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    // RESTO
    Route::get('/restaurants', [RestaurantController::class, 'index']);
    // Créer un nouveau restaurant
    Route::post('/restaurants', [RestaurantController::class, 'store']);
    // Détails d'un restaurant spécifique
    Route::get('/restaurants/{restaurant}', [RestaurantController::class, 'show']);
    // Mettre à jour un restaurant
    Route::put('/restaurants/{restaurant}', [RestaurantController::class, 'update']);
    Route::patch('/restaurants/{restaurant}', [RestaurantController::class, 'update']);
    // Supprimer un restaurant
    Route::delete('/restaurants/{restaurant}', [RestaurantController::class, 'destroy']);

    // INGREDIENTS
    Route::get('/ingredients', [IngredientController::class, 'index']);
    Route::get('/ingredients/{ingredient}', [IngredientController::class, 'show']);

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

