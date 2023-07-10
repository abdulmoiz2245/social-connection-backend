<?php

use App\Http\Controllers\RequestController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    
    // Routes for request features
    Route::group(['prefix' => 'request'], function () {
        //  send a connection request
        Route::post('/', [RequestController::class, 'store'])->name('request.store');
        //  show sent requests data 
        Route::get('/sent', [RequestController::class, 'index'])->name('request.sent');
        
        //  withdraw a connection request
        Route::delete('/{id}', [RequestController::class, 'destroy'])->name('request.destroy');
    });

});

?>
