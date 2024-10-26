<?php

use App\Http\Controllers\Api\v1\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function() {
    Route::get('/info', function () {
        return response()->json([
            'name' => 'Trần Gia Huy',
            'country' => 'Việt Nam',
            'car' => 'Vinfast VF9',
            'motorbike' => 'Honda Winner X 2022',
        ]);
    })->middleware('auth:sanctum');
    
    Route::controller(AuthController::class)->group(function() {
        Route::post('/login', 'login');
        Route::post('/logout', 'logout');
        Route::get('/is-authenticated', 'isAuthenticated');
    });
});