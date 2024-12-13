<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;
use App\Models\User;


//? Groupe Admin
//Route::prefix('admin')->group(function () {
//});


//! Routes ADMIN
Route::middleware([AdminMiddleware::class])->group(function () {

    /**
     * Get All Users (Admin)
     * @authenticated
     * @group User
     */
    Route::get('/users', function () {
        return User::all();
    });

    /**
     * Get User by ID (Admin)
     * @authenticated
     * @group User
     */
    Route::get('/users/{id}', function ($id) {
        return User::findOrFail($id);
    });
});
