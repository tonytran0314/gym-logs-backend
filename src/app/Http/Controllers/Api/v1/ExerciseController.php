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
    public function getMuscles() {
        $muscles = Muscle::all();
        $muscleData = [];
        foreach($muscles as $muscle) {
            $muscleData[] = [
                'name' => $muscle->name,
                'image' => $muscle->image
            ];
        }
        return response()->json($muscleData);
    }
    
    public function getExercises(Request $request) {
        $muscleID = $request->query('muscle_id');
        $exercises = Muscle::find($muscleID)->exercises()->get();
        return response()->json($exercises);
    }

    public function getCurrentExercise(Request $request) {
        $today = Carbon::today();
        $userID = Auth::user()->id;
        $exerciseID = $request->query('exercise_id');
        $exerciseName = Exercise::find($exerciseID)->name;

        // Lấy bản ghi có set_number lớn nhất
        $maxSetNumber = ExerciseRecords::where('user_id', $userID)
            ->where('exercise_id', $exerciseID)
            ->whereDate('created_at', $today)
            ->max('set_number'); // Lấy giá trị set_number lớn nhất

        $setNumber = $maxSetNumber ? $maxSetNumber + 1 : 1; // Nếu có bản ghi, tăng set_number, nếu không, bắt đầu từ 1

        return response()->json([
            'name' => $exerciseName,
            'set' => $setNumber,
        ]);
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
