<?php

namespace App\Http\Controllers\Api\v1\Stats;

use App\Http\Controllers\Controller;
use App\Models\ExerciseRecords;
use App\Models\Exercise;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class MostPopularExerciseAnalysisController extends Controller
{
    use HttpResponses;

    public function index() {
        $userID = Auth::user()->id;
        // Bước 1: Lấy bài tập phổ biến nhất (tính số lần xuất hiện của từng bài tập)
        $mostPopularExercise = ExerciseRecords::where('user_id', $userID)
            ->select('exercise_id')
            ->groupBy('exercise_id')  // Nhóm theo 'exercise'
            ->selectRaw('count(*) as exercise_count')  // Đếm số lần xuất hiện của mỗi bài tập
            ->orderByDesc('exercise_count')  // Sắp xếp theo số lần xuất hiện, giảm dần
            ->first(); // Lấy bài tập phổ biến nhất

        // Nếu không có bài tập nào, trả về thông báo
        if (!$mostPopularExercise) {
            return $this->success(null, 'Not enough data to perform the requested analysis. Please start working out');
        }

        $exerciseName = Exercise::find($mostPopularExercise->exercise_id)->name;

        // Bước 2: Lấy hai ngày tập luyện gần nhất của bài tập phổ biến
        $dates = ExerciseRecords::where('user_id', $userID)
            ->where('exercise_id', $mostPopularExercise->exercise_id)  // Sử dụng 'exercise' để lọc bài tập
            ->select('created_at', 'reps', 'weight_level')
            ->orderByDesc('created_at')
            ->limit(2)
            ->get();

        // Nếu không có đủ 2 ngày tập luyện, trả về thông báo
        if ($dates->count() < 2) {
            return $this->success(null, 'Not enough data to perform the requested analysis. Please start working out');
        }

        // Bước 3: So sánh 2 ngày tập luyện gần nhất
        $firstDay = $dates->first();  // Ngày gần nhất
        $secondDay = $dates->last();  // Ngày trước đó

        // So sánh reps hoặc weight
        $comparisonResult = [
            'direction' => 'increase',
            'value' => 0,
            'metric' => 'reps',
        ];
        $direction = null;
        $valueChange = null;

        // So sánh reps nếu có thay đổi
        if ($firstDay->reps != $secondDay->reps) {
            $direction = $firstDay->reps > $secondDay->reps ? 'increase' : 'decrease';
            $valueChange = abs($firstDay->reps - $secondDay->reps);
            $comparisonResult = [
                'direction' => $direction,
                'value' => $valueChange,
                'metric' => 'reps',
            ];
        }
        // Nếu reps không thay đổi, so sánh weight
        else if ($firstDay->weight_level != $secondDay->weight_level) {
            $direction = $firstDay->weight_level > $secondDay->weight_level ? 'increase' : 'decrease';
            $valueChange = abs($firstDay->weight_level - $secondDay->weight_level);
            $comparisonResult = [
                'direction' => $direction,
                'value' => $valueChange,
                'metric' => 'pounds',
            ];
        }

        return $this->success([
            'exerciseName' => $exerciseName,
            'comparison' => $comparisonResult,
        ], null);
    }
}
