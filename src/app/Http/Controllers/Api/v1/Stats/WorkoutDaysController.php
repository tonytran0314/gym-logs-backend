<?php

namespace App\Http\Controllers\Api\v1\Stats;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\ExerciseRecords;
use App\Traits\HttpResponses;

class WorkoutDaysController extends Controller
{
    use HttpResponses;
    
    public function index() { // workout days this month
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
        return $this->success(count($dates), null);
    }
}
