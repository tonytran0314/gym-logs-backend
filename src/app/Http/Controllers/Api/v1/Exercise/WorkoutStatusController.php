<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ExerciseRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Muscle;
use App\Models\WorkoutStatus;
use App\Models\Exercise;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class ExerciseController extends Controller
{
    // public function isWorkingout() {
    //     $userID = Auth::user()->id;
    //     $status = WorkoutStatus::where('user_id', $userID)->get();
    //     if ($status->isNotEmpty()) {
    //         return response()->json([
    //             'isWorkingout' => true,
    //         ]); 
    //     }

    //     return response()->json([
    //         'isWorkingout' => false,
    //     ]); 
    // }

    // public function startWorkout() {
    //     $userID = Auth::user()->id;
    //     $status = WorkoutStatus::create([
    //         'user_id' => $userID
    //     ]);
    //     $status->save();
    // }

    // public function stopWorkout() {
    //     $userID = Auth::user()->id;
    //     $status = WorkoutStatus::where('user_id', $userID);
    //     $status->delete();
    // }
}
