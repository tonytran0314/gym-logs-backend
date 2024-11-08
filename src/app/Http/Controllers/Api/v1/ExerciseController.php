<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ExerciseRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;

class ExerciseController extends Controller
{
    public function isWorkingout() {
        
        if (Auth::user()->isWorkingout) {
            return response()->json([
                'isWorkingout' => true,
            ]); 
        }

        return response()->json([
            'isWorkingout' => false,
        ]); 
    }

    public function startWorkout() {
        $user = User::find(Auth::user()->id);
        $user->isWorkingout = true;
        $user->save();
    }

    public function stopWorkout() {
        $user = User::find(Auth::user()->id);
        $user->isWorkingout = false;
        $user->save();
    }

    public function saveSet(Request $request) {

        // Dùng cái trượt chọn số giống Apple để không cần validation

        $userID = Auth::user()->id;

        $set = $request->all();
    
        // Looking for the previous set (the same user, the same date, the same exercise), then the set number + 1. 
        // If cannot find the prev set, means this is the first set => set number = 1
        $previousSets = ExerciseRecords::where([
                                                ['user_id', $userID],
                                                ['exercise', $set['exercise']]
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
