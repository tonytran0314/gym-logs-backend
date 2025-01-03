<?php

namespace App\Http\Controllers\Api\v1\Charts;

use App\Http\Controllers\Controller;
use App\Models\ExerciseRecords;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MuscleProportionsController extends Controller
{
    use HttpResponses;

    public function index() {
        $userID = Auth::user()->id;

        // Lấy ngày hôm nay
        $today = Carbon::today();

        $muscleGroups = ExerciseRecords::select('muscles.name as muscle_name', DB::raw('count(*) as count'))
            ->join('muscles', 'exercise_records.muscle_id', '=', 'muscles.id')
            ->where('exercise_records.user_id', $userID)
            ->whereDate('exercise_records.created_at', '<=', $today) // Lọc các records từ hôm nay trở về trước
            ->groupBy('exercise_records.muscle_id', 'muscles.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $muscleNames = $muscleGroups->pluck('muscle_name');
        $counts = $muscleGroups->pluck('count');

        if(count($muscleNames) < 2 || count($counts) < 2) {
            return $this->success(null, 'Not enough data to perform the requested analysis. Please start working out');
        }
        
        return $this->success([
            'muscle_groups' => $muscleNames,
            'counts' => $counts,
        ], null);
    }
}
