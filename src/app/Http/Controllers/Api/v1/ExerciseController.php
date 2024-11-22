<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ExerciseRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Muscle;
use App\Models\WorkoutStatus;
use Carbon\Carbon;

class ExerciseController extends Controller
{
    public function getMuscles() {
        $muscles = Muscle::get();
        return response()->json($muscles);
    }
    
    public function getExercises(Request $request) {
        $muscleID = $request->query('muscle_id');
        $exercises = Muscle::find($muscleID)->exercises()->get();
        return response()->json($exercises);
    }

    public function isWorkingout() {
        $userID = Auth::user()->id;
        $status = WorkoutStatus::where('user_id', $userID)->get();
        if ($status->isNotEmpty()) {
            return response()->json([
                'isWorkingout' => true,
            ]); 
        }

        return response()->json([
            'isWorkingout' => false,
        ]); 
    }

    public function startWorkout() {
        $userID = Auth::user()->id;
        $status = WorkoutStatus::create([
            'user_id' => $userID
        ]);
        $status->save();
    }

    public function stopWorkout() {
        $userID = Auth::user()->id;
        $status = WorkoutStatus::where('user_id', $userID);
        $status->delete();
    }

    public function saveSet(Request $request) {

        // Dùng cái trượt chọn số giống Apple để không cần validation

        $userID = Auth::user()->id;

        $set = $request->all();
    
        // Looking for the previous set (the same user, the same date, the same exercise), then the set number + 1. 
        // If cannot find the prev set, means this is the first set => set number = 1
        $previousSets = ExerciseRecords::where([
                                                ['user_id', $userID],
                                                ['exercise_id', $set['exercise_id']]
                                            ])
                                            ->whereDate('created_at', Carbon::today())
                                            ->get();

        // ex: finished 3 sets, the array return 3 elements (length = 3)
        // Now this is set # 4 = length + 1 = 3 + 1
        $currentSetNumber = $previousSets->count() + 1;
        
        $set['set_number'] = $currentSetNumber;
        $set['user_id'] = $userID;

        $newSet = ExerciseRecords::create($set); 

        if($newSet) {
            return response()->json([
                'message' => 'Record added.',
            ], 200);
        }
        
        return response()->json([
            'message' => 'Failed to add record',
        ], 500);
    }
}
