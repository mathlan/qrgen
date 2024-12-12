<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RestaurantController;

Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::get('/test', function () {
    return response()->json(['status' => 'success']);
});


Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Routes de restaurants protégées par l'authentification
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('restaurants', RestaurantController::class);
});
