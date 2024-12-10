<?php

namespace App\Http\Controllers\Api\v1\Stats;

use App\Http\Controllers\Controller;
use App\Models\ExerciseRecords;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TotalExercisesThisWeekController extends Controller
{
    use HttpResponses;

    public function index() {
        $userID = Auth::user()->id;
        // Lấy ngày bắt đầu của tuần hiện tại (chủ nhật tuần trước)
        $startOfWeek = Carbon::now()->startOfWeek(); // Mặc định là chủ nhật tuần trước
        $endOfWeek = Carbon::now()->endOfWeek(); // Cuối tuần (thứ bảy tuần này)

        // Truy vấn các bài tập thực hiện trong tuần này của người dùng
        $totalExercises = ExerciseRecords::where('user_id', $userID)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek]) // Lọc theo ngày trong tuần này
            ->distinct('exercise_id')
            ->count(); // Đếm tổng số bài tập

        // Trả về tổng số bài tập
        return $this->success($totalExercises, null);
    }
}
