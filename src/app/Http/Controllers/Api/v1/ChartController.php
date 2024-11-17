<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ExerciseRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChartController extends Controller
{
    public function weightLevel($selectedExercise = null) {
        $userID = Auth::user()->id;

        $records = ExerciseRecords::where('user_id', $userID)->get();

        // Nhóm các bài tập theo tên và ngày tập luyện (loại bỏ giờ phút)
        $exerciseDays = $records->groupBy(function ($record) {
            return $record['exercise'] . '|' . Carbon::parse($record['created_at'])->format('Y-m-d');
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
            ]);
        }

        $mostCommonExercise = collect($records)
                                ->pluck('exercise')
                                ->countBy()
                                ->sortDesc()
                                ->keys()
                                ->first();

        $exercise = $selectedExercise ?? $mostCommonExercise;

        if (!in_array($exercise, $exercises)) {
            $exercise = $exercises[0]; // chọn exercise đầu tiên nếu exercise phổ biến nhất không đủ điều kiện
        }

        $data = $records->where('exercise', $exercise);

        // Tiếp tục xử lý để lấy dữ liệu cân nặng theo ngày
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
            'exercises' => $exercises,
        ]);

    }
}
