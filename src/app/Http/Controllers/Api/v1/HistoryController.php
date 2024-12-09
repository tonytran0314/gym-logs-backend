<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ExerciseRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Exercise;
use App\Models\Muscle;
use App\Traits\HttpResponses;

class HistoryController extends Controller
{

    use HttpResponses;

    // This should have pagination
    public function index() {
        $userID = Auth::user()->id;
        $records = ExerciseRecords::where('user_id', $userID)->orderByDesc('created_at')->get();

        // Nhóm dữ liệu theo ngày
        $groupedRecords = $records->groupBy(function ($item) {
            return $item->created_at->format('D, M d');  // Định dạng ngày thành Nov 20, 2024
        });

        // Biến đổi dữ liệu thành cấu trúc phù hợp với yêu cầu
        $workouts = $groupedRecords->map(function ($workoutRecords, $date) {
            $exercises = $workoutRecords->groupBy('exercise_id')->map(function ($exerciseRecords) {
                return [
                    'name' => Exercise::find($exerciseRecords->first()->exercise_id)->name,
                    'muscle' => Muscle::find($exerciseRecords->first()->muscle_id)->name,
                    'sets' => $exerciseRecords->map(function ($record) {
                        return [
                            'set_number' => $record->set_number,
                            'weight_lbs' => $record->weight_level,
                            'reps' => $record->reps,
                        ];
                    }),
                ];
            });

            return [
                'time' => [
                    'day' => $workoutRecords->first()->created_at->format('D'),
                    'date' => $workoutRecords->first()->created_at->format('M d'),
                    'year' => $workoutRecords->first()->created_at->format('Y')
                ],
                'exercises' => $exercises->values(),
            ];
        });

        return $this->success($workouts->values(), null);
    }
}
