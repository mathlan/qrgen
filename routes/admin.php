<?php

use App\Http\Controllers\IngredientController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;
use App\Models\User;

//! Routes ADMIN
Route::middleware([AdminMiddleware::class])->group(function () {

    //? Groupe Admin
    Route::prefix('admin')->group(function () {
        // USER
        /**
         * Get All Users (Admin)
         * @authenticated
         * @group User
         */
        Route::get('/users', function () {
            return User::all();
        });
        Route::get('/users/{id}', function ($id) {
            return User::findOrFail($id);
        });

        // INGREDIENTS
        Route::post('/ingredients', [IngredientController::class, 'store']);
        Route::put('/ingredients/{ingredient}', [IngredientController::class, 'update']);
        Route::patch('/ingredients/{ingredient}', [IngredientController::class, 'update']);
        Route::delete('/ingredients/{ingredient}', [IngredientController::class, 'destroy']);
    });
});
