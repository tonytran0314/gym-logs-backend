<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ExerciseRecords;
use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ArchivementController extends Controller
{
    public function getTotalExerciseThisWeek() {
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
        return response()->json([
            'totalExercises' => $totalExercises
        ]);
    }
}
