<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Middleware\AdminMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RestaurantController;

//* PUBLIC (no auth)
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

//* PRIVATE (JWT auth)
Route::middleware('jwt.auth')->group(function () {

    // AUTH
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);

    //USER
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });


    //RESTO
    Route::apiResource('restaurants', RestaurantController::class);

    //TEST
    Route::get('/test', function () {
        return response()->json(['status' => 'success']);
    });

    //! ADMIN
    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::get('/users', function () {
            return User::all();
        });

        Route::get('/users/{id}', function ($id) {
            return User::findOrFail($id);
        });
    });
});




//Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//    return $request->user();
//});

