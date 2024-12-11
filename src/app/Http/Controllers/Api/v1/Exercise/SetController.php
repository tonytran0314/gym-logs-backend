<?php

namespace App\Http\Controllers\Api\v1\Exercise;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\StoreSetRequest;
use App\Models\ExerciseRecords;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SetController extends Controller
{
    use HttpResponses;

    public function store(StoreSetRequest $request) {
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
            return $this->success(null, 'Record added');
        }
        
        return $this->error(null, 'Failed to add record', 500);
    }
}
