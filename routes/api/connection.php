<?php

use App\Http\Controllers\ConnectionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Routes for connection features
    Route::group(['prefix' => 'connection'], function () {
        //  show received requests page
        Route::get('/received', [ConnectionController::class, 'create'])->name('connection.received');
        //  accept a connection request
        Route::post('/', [ConnectionController::class, 'store'])->name('connection.store');
        //  show connections page
        Route::get('/', [ConnectionController::class, 'index'])->name('connection.index');
        //  show connections in common with a user
        Route::get('/{id}', [ConnectionController::class, 'show'])->name('connection.show');
        //  remove a connection
        Route::delete('/{id}', [ConnectionController::class, 'destroy'])->name('connection.destroy');
    });

});

?>
