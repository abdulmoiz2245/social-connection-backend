<?php

use App\Http\Controllers\Auth\ApiAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::post('/login', [ApiAuthController::class, 'show'])->name('login.api');
Route::post('/register', [ApiAuthController::class, 'store'])->name('register.api');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ApiAuthController::class, 'logout'])->name('logout.api');

    Route::group(['prefix' => 'user'], function () {
        //  show suggestions page
        Route::get('/suggestions', [UserController::class, 'index'])->name('user.suggestions');
       
    });
});
