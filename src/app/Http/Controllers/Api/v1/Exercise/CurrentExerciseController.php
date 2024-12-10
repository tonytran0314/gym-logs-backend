<?php

namespace App\Http\Controllers\Api\v1\Exercise;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exercise;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\ExerciseRecords;
use App\Traits\HttpResponses;

class CurrentExerciseController extends Controller
{
    use HttpResponses;

    public function index(Request $request) {
        $today = Carbon::today();
        $userID = Auth::user()->id;
        $exerciseID = $request->exercise_id;
        $exerciseName = Exercise::find($exerciseID)->name;

        // Lấy bản ghi có set_number lớn nhất
        $maxSetNumber = ExerciseRecords::where('user_id', $userID)
            ->where('exercise_id', $exerciseID)
            ->whereDate('created_at', $today)
            ->max('set_number'); // Lấy giá trị set_number lớn nhất

        $setNumber = $maxSetNumber ? $maxSetNumber + 1 : 1; // Nếu có bản ghi, tăng set_number, nếu không, bắt đầu từ 1

        return $this->success([
            'name' => $exerciseName,
            'set' => $setNumber,
        ], null);
    }
}
