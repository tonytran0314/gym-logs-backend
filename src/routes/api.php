<?php

use App\Http\Controllers\Api\v1\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\ExerciseController;
use App\Http\Controllers\Api\v1\ProfileController;

Route::prefix('v1')->group(function() {
    Route::middleware('auth:sanctum')->group(function() {

        Route::get('/info', function () {
            return response()->json([
                'name' => 'Trần Gia Huy',
                'country' => 'Việt Nam',
                'car' => 'Vinfast VF9',
                'motorbike' => 'Honda Winner X 2022',
            ]);
        });
        
        Route::controller(ExerciseController::class)->group(function() {
            Route::get('/is-workingout', 'isWorkingout');
            Route::put('/start-workout', 'startWorkout');
            Route::put('/stop-workout', 'stopWorkout');
            Route::post('/save-set', 'saveSet');
        });

        Route::controller(ProfileController::class)->group(function() {
            Route::get('/profile', 'profile');
            Route::put('/profile', 'editProfile');
        });
    });

    Route::controller(AuthController::class)->group(function() {
        Route::post('/login', 'login');
        Route::post('/logout', 'logout');
        Route::post('/signup', 'signup');
        Route::get('/is-authenticated', 'isAuthenticated');
    });
});