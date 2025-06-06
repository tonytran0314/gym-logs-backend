<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\Api\v1\Charts\MuscleProportionsController;
use App\Http\Controllers\Api\v1\Charts\WeightLevelsController;

use App\Http\Controllers\Api\v1\Exercise\CurrentExerciseController;
use App\Http\Controllers\Api\v1\Exercise\ExerciseController;
use App\Http\Controllers\Api\v1\Exercise\HistoryController;
use App\Http\Controllers\Api\v1\Exercise\MuscleController;
use App\Http\Controllers\Api\v1\Exercise\SetController;

use App\Http\Controllers\Api\v1\User\AuthController;
use App\Http\Controllers\Api\v1\User\ProfileController;

use App\Http\Controllers\Api\v1\Stats\MostPopularExerciseAnalysisController;
use App\Http\Controllers\Api\v1\Stats\StreakController;
use App\Http\Controllers\Api\v1\Stats\TotalExercisesThisWeekController;
use App\Http\Controllers\Api\v1\Stats\WorkoutDaysController;

use App\Http\Controllers\Api\v1\Exercise\RecentWorkoutController;

Route::prefix('v1')->group(function() {
    Route::middleware('auth:sanctum')->group(function() {
        Route::apiResource('/muscles', MuscleController::class)->only(['index']);
        Route::apiResource('/exercises', ExerciseController::class)->only(['index']);
        Route::apiResource('/history', HistoryController::class)->only(['index']);
        Route::apiResource('/current-exercise', CurrentExerciseController::class)->only(['index']);
        Route::apiResource('/set', SetController::class)->only(['store']);

        Route::controller(ProfileController::class)->group(function() {
            Route::get('/profile', 'show');
            Route::put('/profile', 'update');
        });

        Route::prefix('charts')->group(function() {
            Route::apiResource('/muscle-proportions', MuscleProportionsController::class)->only(['index']);
            Route::apiResource('/weight-levels', WeightLevelsController::class)->only(['index']);
        });
        
        Route::prefix('stats')->group(function() {
            Route::apiResource('/streak', StreakController::class)->only(['index']);
            Route::apiResource('/workout-days', WorkoutDaysController::class)->only(['index']);
            Route::apiResource('/most-popular-exercise-analysis', MostPopularExerciseAnalysisController::class)->only(['index']);
            Route::apiResource('/total-exercise-this-week', TotalExercisesThisWeekController::class)->only(['index']);
        });

        Route::get('/recent-workouts', [RecentWorkoutController::class, 'index']);
    });

    Route::controller(AuthController::class)->group(function() {
        Route::post('/login', 'login');
        Route::post('/logout', 'logout');
        Route::post('/signup', 'signup');
        Route::get('/is-authenticated', 'isAuthenticated');
    });
});