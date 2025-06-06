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

        $muscleGroups = ExerciseRecords::select(
            'muscles.name as muscle_name', 
            DB::raw('count(*) as count'),
            DB::raw('(count(*) * 100.0 / (SELECT count(*) FROM exercise_records WHERE user_id = ' . $userID . ' AND DATE(created_at) <= "' . $today . '")) as percentage')
        )
            ->join('muscles', 'exercise_records.muscle_id', '=', 'muscles.id')
            ->where('exercise_records.user_id', $userID)
            ->whereDate('exercise_records.created_at', '<=', $today)
            ->groupBy('exercise_records.muscle_id', 'muscles.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        if($muscleGroups->count() < 2) {
            return $this->success([], 'Not enough data to perform the requested analysis. Please start working out');
        }

        $formattedData = $muscleGroups->map(function($item) {
            return [
                'name' => $item->muscle_name,
                'total' => round($item->percentage, 1) // Round to 1 decimal place
            ];
        });
        
        return $this->success($formattedData, null);
    }
}
