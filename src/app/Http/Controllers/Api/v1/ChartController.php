<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ExerciseRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Exercise;

class ChartController extends Controller
{
    public function weightLevel($selectedExercise = null, $months = 1) {
        $userID = Auth::user()->id;

        // Xác định ngày bắt đầu (tính từ hôm nay trừ đi số tháng)
        $startDate = Carbon::now()->subMonths($months)->startOfDay();

        // Lọc các bản ghi theo user_id và created_at trong khoảng thời gian đã chọn, bao gồm bài tập
        $records = ExerciseRecords::with('exercise') // Eager load exercise
                    ->where('user_id', $userID)
                    ->where('created_at', '>=', $startDate)
                    ->get();

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

        // Nếu không có bài tập nào đủ điều kiện, trả về mảng rỗng
        if (empty($exercises)) {
            return response()->json([
                'dates' => [],
                'weight_levels' => [],
                'exercises' => [],
                'error' => 'No exercises meet the criteria.',
            ]);
        }

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
            return response()->json([
                'dates' => [],
                'weight_levels' => [],
                'exercises' => [],
                'error' => 'No valid exercise available.',
            ]);
        }

        // Lấy tên bài tập
        $exerciseName = $records->firstWhere('exercise_id', $exercise)->exercise->name ?? 'Unknown Exercise';

        // Tiếp tục xử lý dữ liệu
        $data = $records->where('exercise_id', $exercise);

        $weightData = $data
                        ->groupBy(function ($record) {
                            return Carbon::parse($record['created_at'])->format('Y-m-d');
                        })
                        ->map(function ($recordsByDay) {
                            $totalWeight = $recordsByDay->sum(function ($record) {
                                return $record['reps'] * $record['weight_level'];
                            });

                            return [
                                'date' => $recordsByDay->first()['created_at']->format('Y-m-d'),
                                'weight_level' => $totalWeight,
                            ];
                        })
                        ->values()
                        ->toArray();

        $dates = array_column($weightData, 'date');
        $weightLevels = array_column($weightData, 'weight_level');

        return response()->json([
            'dates' => $dates,
            'weight_levels' => $weightLevels,
            'exercises' => $exerciseList, // Danh sách bài tập (ID và tên)
            'exercise' => [
                'id' => $exercise,
                'name' => $exerciseName
            ],
            'periods' => [
                ['label' => '1 month', 'value' => 1],
                ['label' => '2 months', 'value' => 2],
                ['label' => '3 months', 'value' => 3],
            ]
        ]);
    }

    public function muscleProportions() {
        $userID = Auth::user()->id;

        $muscleGroups = ExerciseRecords::select('muscles.name as muscle_name', DB::raw('count(*) as count'))
            ->join('muscles', 'exercise_records.muscle_id', '=', 'muscles.id')
            ->where('exercise_records.user_id', $userID)
            ->groupBy('exercise_records.muscle_id', 'muscles.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $muscleNames = $muscleGroups->pluck('muscle_name');
        $counts = $muscleGroups->pluck('count');
        
        return response()->json([
            'muscle_groups' => $muscleNames,
            'counts' => $counts,
        ]);
    }
}
