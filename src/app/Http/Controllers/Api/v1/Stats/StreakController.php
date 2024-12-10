<?php

namespace App\Http\Controllers\Api\v1\Stats;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ExerciseRecords;
use App\Traits\HttpResponses;
use Carbon\Carbon;

class StreakController extends Controller
{

    use HttpResponses;

    public function index() {
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

        return $this->success($streak, null);
    }
}
