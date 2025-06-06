<?php

namespace App\Http\Controllers\Api\v1\Charts;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\WeightLevelsChartRequest;
use App\Models\ExerciseRecords;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Exercise;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class WeightLevelsController extends Controller
{
    use HttpResponses;

    public function index(WeightLevelsChartRequest $request) {
        $userID = Auth::user()->id;

        $selectedExercise = $request->exercise;
        $months = $request->periodInMonths;

        // Xác định ngày bắt đầu (tính từ hôm nay trừ đi số tháng)
        $startDate = Carbon::now()->subMonths($months)->startOfDay();
        $today = Carbon::today(); // Lấy ngày hôm nay

        // Lọc các bản ghi theo user_id và created_at trong khoảng thời gian đã chọn, bao gồm bài tập
        $records = ExerciseRecords::with('exercise') // Eager load exercise
                    ->where('user_id', $userID)
                    ->whereBetween('created_at', [$startDate, $today]) // Lọc từ ngày bắt đầu đến hôm nay
                    ->get();
        
        if (count($records) < 2) {
            return $this->success([], 'Not enough data to perform the requested analysis. Please start working out');
        }

        // Nhóm các bài tập theo tên và ngày tập luyện (loại bỏ giờ phút)
        $exerciseDays = $records->groupBy(function ($record) {
            return $record['exercise_id'] . '|' . Carbon::parse($record['created_at'])->format('Y-m-d');
        });

        // Đếm số ngày khác nhau cho mỗi bài tập
        $exerciseCount = $exerciseDays->mapToGroups(function ($item, $key) {
            [$exercise] = explode('|', $key);
            return [$exercise => 1];
        })->map->count();

        // Lấy các bài tập có ít nhất 2 ngày tập luyện trở lên
        $exercises = $exerciseCount->filter(function ($count) {
            return $count >= 2;
        })->keys()->toArray();

        // Lấy danh sách bài tập (id và name)
        $exerciseList = Exercise::whereIn('id', $exercises)
                        ->get(['id', 'name'])
                        ->toArray();

        // Xác định bài tập phổ biến nhất
        $mostCommonExercise = collect($records)
                                ->pluck('exercise_id')
                                ->countBy()
                                ->sortDesc()
                                ->keys()
                                ->first();

        // Kiểm tra bài tập được chọn hoặc mặc định
        $exercise = ($selectedExercise === null) ? $mostCommonExercise : $selectedExercise;

        // Kiểm tra bài tập được chọn có nằm trong danh sách hợp lệ không
        if (!in_array($exercise, $exercises)) {
            $exercise = $exercises[0] ?? null; // Chọn bài tập đầu tiên nếu không hợp lệ
        }

        // Nếu không có bài tập hợp lệ, trả về lỗi
        if (!$exercise) {
            return $this->success([], 'Not enough data to perform the requested analysis. Please start working out');
        }

        // Lấy tên bài tập
        $exerciseName = $records->firstWhere('exercise_id', $exercise)->exercise->name ?? 'Unknown Exercise';

        // Tiếp tục xử lý dữ liệu
        $data = $records->where('exercise_id', $exercise);

        $chartData = $data
            ->groupBy(function ($record) {
                return Carbon::parse($record['created_at'])->format('Y-m-d');
            })
            ->map(function ($recordsByDay) {
                $totalWeight = $recordsByDay->sum(function ($record) {
                    return $record['reps'] * $record['weight_level'];
                });

                return [
                    'dates' => Carbon::parse($recordsByDay->first()['created_at'])->format('M d'),
                    'value' => $totalWeight,
                ];
            })
            ->values()
            ->toArray();

        return $this->success([
            'data' => $chartData,
            'exercises' => $exerciseList, // Danh sách bài tập (ID và tên)
            'exercise' => [
                'id' => $exercise,
                'name' => $exerciseName
            ],
            'periods' => [
                ['label' => 'Last month', 'value' => 1],
                ['label' => 'Last 2 months', 'value' => 2],
                ['label' => 'Last 3 months', 'value' => 3],
                ['label' => 'Last 6 months', 'value' => 6],
                ['label' => 'Last year', 'value' => 12],
            ]
        ], null);
    }
}
