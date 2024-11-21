<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ExerciseRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function getHistoryRecords() {
        $userID = Auth::user()->id;
        $records = ExerciseRecords::where('user_id', $userID)->orderByDesc('created_at')->get();

        // Nhóm dữ liệu theo ngày
        $groupedRecords = $records->groupBy(function ($item) {
            return $item->created_at->format('M d, Y');  // Định dạng ngày thành Nov 20, 2024
        });

        // Biến đổi dữ liệu thành cấu trúc phù hợp với yêu cầu
        $workouts = $groupedRecords->map(function ($workoutRecords, $date) {
            $exercises = $workoutRecords->groupBy('exercise')->map(function ($exerciseRecords, $exerciseName) {
                return [
                    'name' => $exerciseName,
                    'muscle' => $exerciseRecords->first()->muscle,
                    'sets' => $exerciseRecords->map(function ($record) {
                        return [
                            'set_number' => $record->set_number,
                            'weight_lbs' => $record->weight_level,
                            'reps' => $record->reps,
                        ];
                    }),
                ];
            });

            // Tính tổng số sets cho ngày đó
            $totalSets = $workoutRecords->count();

            return [
                'date' => $date,  // Ngày đã được định dạng
                'total_sets' => $totalSets,  // Tổng số sets trong ngày
                'exercises' => $exercises->values(),
            ];
        });

        return response()->json($workouts->values());
    }
}
