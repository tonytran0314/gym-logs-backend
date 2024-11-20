<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ExerciseRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ArchivementController extends Controller
{
    public function getStreak() {

        $userID = Auth::user()->id;
        $dates = ExerciseRecords::where('user_id', $userID)
            ->select('created_at')
            ->distinct()
            ->orderBy('created_at', 'desc')
            ->get()
            ->pluck('created_at')
            ->map(fn($date) => Carbon::parse($date)) // Chuyển về Carbon
            ->toArray();

        // Nếu không có dữ liệu, trả về streak là 0 và danh sách rỗng
        if (empty($dates)) {
            return [
                'current_streak' => 0,
                'current_streak_dates' => [],
            ];
        }

        // Khởi tạo biến tính streak và danh sách ngày liên tục
        $streak = 0;
        $streakDates = [];
        $yesterday = Carbon::today(); // Bắt đầu từ hôm nay

        foreach ($dates as $date) {
            if ($date->isSameDay($yesterday)) {
                $streak++; // Tăng streak
                $streakDates[] = $date->toDateString(); // Thêm ngày vào danh sách
                $yesterday = $yesterday->subDay(); // Lùi về ngày trước
            } elseif ($date->isBefore($yesterday)) {
                break; // Nếu có ngày gián đoạn, dừng lại
            }
        }

        return response()->json([
            'current_streak' => $streak,
        ]);
    }

    public function getWorkoutDays() {
        $userID = Auth::user()->id;

        $startOfMonth = Carbon::now()->startOfMonth(); // Ngày đầu tháng
        $endOfMonth = Carbon::now()->endOfMonth(); // Ngày cuối tháng

        $dates = ExerciseRecords::where('user_id', $userID)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->selectRaw('DISTINCT DATE(created_at) as date') // Lọc ngày duy nhất
                ->get()
                ->pluck('created_at') // Lấy cột 'date' từ kết quả
                ->map(fn($date) => Carbon::parse($date)) // Chuyển về đối tượng Carbon
                ->toArray();

        // Trả về tổng số ngày tập luyện trong tháng
        return response()->json([
            'workoutDays' => count($dates)
        ]);
    }

    public function getMostPopularExerciseComparison() {
        $userID = Auth::user()->id;
        // Bước 1: Lấy bài tập phổ biến nhất (tính số lần xuất hiện của từng bài tập)
        $mostPopularExercise = ExerciseRecords::where('user_id', $userID)
            ->select('exercise')  // Sử dụng 'exercise' thay cho 'name'
            ->groupBy('exercise')  // Nhóm theo 'exercise'
            ->selectRaw('count(*) as exercise_count')  // Đếm số lần xuất hiện của mỗi bài tập
            ->orderByDesc('exercise_count')  // Sắp xếp theo số lần xuất hiện, giảm dần
            ->first(); // Lấy bài tập phổ biến nhất

        // Nếu không có bài tập nào, trả về thông báo
        if (!$mostPopularExercise) {
            return response()->json([
                'message' => 'No exercises found for this user.',
            ]);
        }

        $exerciseName = $mostPopularExercise->exercise;

        // Bước 2: Lấy hai ngày tập luyện gần nhất của bài tập phổ biến
        $dates = ExerciseRecords::where('user_id', $userID)
            ->where('exercise', $exerciseName)  // Sử dụng 'exercise' để lọc bài tập
            ->select('created_at', 'reps', 'weight_level')
            ->orderByDesc('created_at')
            ->limit(2)
            ->get();

        // Nếu không có đủ 2 ngày tập luyện, trả về thông báo
        if ($dates->count() < 2) {
            return response()->json([
                'message' => 'Not enough data to compare.',
            ]);
        }

        // Bước 3: So sánh 2 ngày tập luyện gần nhất
        $firstDay = $dates->first();  // Ngày gần nhất
        $secondDay = $dates->last();  // Ngày trước đó

        // So sánh reps hoặc weight
        $comparisonResult = [];
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
        elseif ($firstDay->weight_level != $secondDay->weight_level) {
            $direction = $firstDay->weight_level > $secondDay->weight_level ? 'increase' : 'decrease';
            $valueChange = abs($firstDay->weight_level - $secondDay->weight_level);
            $comparisonResult = [
                'direction' => $direction,
                'value' => $valueChange,
                'metric' => 'weight_level',
            ];
        }

        // Bước 4: Trả về kết quả
        return response()->json([
            'exerciseName' => $exerciseName,
            'comparison' => $comparisonResult,
        ]);
    }

    public function getTotalExerciseThisWeek() {
        $userID = Auth::user()->id;
        // Lấy ngày bắt đầu của tuần hiện tại (chủ nhật tuần trước)
        $startOfWeek = Carbon::now()->startOfWeek(); // Mặc định là chủ nhật tuần trước
        $endOfWeek = Carbon::now()->endOfWeek(); // Cuối tuần (thứ bảy tuần này)

        // Truy vấn các bài tập thực hiện trong tuần này của người dùng
        $totalExercises = ExerciseRecords::where('user_id', $userID)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek]) // Lọc theo ngày trong tuần này
            ->distinct('exercise')
            ->count(); // Đếm tổng số bài tập

        // Trả về tổng số bài tập
        return response()->json([
            'totalExercises' => $totalExercises
        ]);
    }
}
