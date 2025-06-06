<?php

namespace App\Http\Controllers\Api\v1\Exercise;

use App\Http\Controllers\Controller;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\ExerciseRecords;
use App\Models\Exercise;
use App\Models\Muscle;

class RecentWorkoutController extends Controller {

    use HttpResponses;

    public function index() {
        $userID = Auth::user()->id;

        $today = Carbon::now();

        // Lọc các bản ghi trong khoảng thời gian từ hôm nay trở về trước
        $records = ExerciseRecords::where('user_id', $userID)
            ->where('created_at', '<=', $today) // Điều kiện ngày
            ->orderByDesc('created_at')
            ->get();
    
        // Kiểm tra xem có bản ghi nào không
        if ($records->isEmpty()) {
            return $this->success(null, 'The workout days history is currently empty. Please start working out');
        }
    
        // Nhóm các bản ghi theo ngày
        $groupedRecords = $records->groupBy(function ($item) {
            return $item->created_at->format('D, M d'); // Nhóm theo ngày (ví dụ: "Tue, Dec 19")
        });
    
        // Chuyển các bản ghi nhóm thành danh sách các ngày tập luyện và chỉ lấy 2 bản ghi gần nhất
        $recentWorkouts = $groupedRecords->take(2)->map(function ($workoutRecords, $date) {
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

        return $this->success($recentWorkouts->values(), null);
    }
}
