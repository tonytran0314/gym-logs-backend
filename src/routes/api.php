<?php

use App\Http\Controllers\Api\v1\Stats\StreakController;
use App\Http\Controllers\Api\v1\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\ExerciseController;
use App\Http\Controllers\Api\v1\ProfileController;
use App\Http\Controllers\Api\v1\ChartController;
use App\Http\Controllers\Api\v1\ArchivementController;
use App\Http\Controllers\Api\v1\HistoryController;
use App\Http\Controllers\Api\v1\Stats\MostPopularExerciseAnalysisController;
use App\Http\Controllers\Api\v1\Stats\WorkoutDaysController;

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
            Route::get('/muscles', 'getMuscles');
            Route::get('/exercises', 'getExercises');
            Route::get('/current-exercise', 'getCurrentExercise');
            Route::get('/is-workingout', 'isWorkingout');
            Route::put('/start-workout', 'startWorkout');
            Route::put('/stop-workout', 'stopWorkout');
            Route::post('/save-set', 'saveSet');
        });

        Route::controller(ProfileController::class)->group(function() {
            Route::get('/profile', 'show');
            Route::put('/profile', 'update');
        });
        // Route::apiResource('/profile', ProfileController::class)->only(['show', 'update']);

        Route::controller(ChartController::class)->prefix('chart')->group(function() {
            Route::get('/weight-level/{selectedExercise?}/{months?}', 'weightLevel');
            Route::get('/muscle-proportions', 'muscleProportions');
        });
        
        Route::prefix('stats')->group(function() {
            Route::apiResource('/streak', StreakController::class)->only(['index']);
            Route::apiResource('/workout-days', WorkoutDaysController::class)->only(['index']);
            Route::apiResource('/most-popular-exercise-analysis', MostPopularExerciseAnalysisController::class)->only(['index']);
        });

        Route::controller(ArchivementController::class)->prefix('archivement')->group(function() {
            Route::get('/popular-exercise-comparison', 'getMostPopularExerciseComparison');
            Route::get('/total-exercise-this-week', 'getTotalExerciseThisWeek');
        });

        Route::apiResource('history', HistoryController::class)->only(['index']);
    });

    Route::controller(AuthController::class)->group(function() {
        Route::post('/login', 'login');
        Route::post('/logout', 'logout');
        Route::post('/signup', 'signup');
        Route::get('/is-authenticated', 'isAuthenticated');
    });
});