<?php

namespace App\Http\Controllers\Api\v1\Charts;

use App\Http\Controllers\Controller;
use App\Models\ExerciseRecords;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MuscleProportionsController extends Controller
{
    use HttpResponses;

    public function index() {
        $userID = Auth::user()->id;

        $muscleGroups = ExerciseRecords::select('muscles.name as muscle_name', DB::raw('count(*) as count'))
            ->join('muscles', 'exercise_records.muscle_id', '=', 'muscles.id')
            ->where('exercise_records.user_id', $userID)
            ->groupBy('exercise_records.muscle_id', 'muscles.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $muscleNames = $muscleGroups->pluck('muscle_name');
        $counts = $muscleGroups->pluck('count');
        
        return $this->success([
            'muscle_groups' => $muscleNames,
            'counts' => $counts,
        ], null);
    }
}
