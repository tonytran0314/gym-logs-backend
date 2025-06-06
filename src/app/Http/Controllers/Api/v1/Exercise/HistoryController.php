<?php
namespace App\Http\Controllers\Api\v1\Exercise;

use App\Http\Controllers\Controller;
use App\Models\ExerciseRecords;
use Illuminate\Support\Facades\Auth;
use App\Models\Exercise;
use App\Models\Muscle;
use App\Traits\HttpResponses;
use Carbon\Carbon;

class HistoryController extends Controller
{
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
            return $this->success([], 'The workout days history is currently empty. Please start working out');
        }
    
        // Nhóm các bản ghi theo ngày
        $groupedRecords = $records->groupBy(function ($item) {
            return $item->created_at->format('D, M d'); // Nhóm theo ngày (ví dụ: "Tue, Dec 19")
        });
    
        // Chuyển các bản ghi nhóm thành danh sách các ngày tập luyện
        $workoutDays = $groupedRecords->map(function ($workoutRecords, $date) {
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
    
        // Phân trang các ngày tập luyện (sau khi nhóm theo ngày)
        $perPage = 8; // Số ngày tập luyện mỗi trang
        $currentPage = (int) request()->get('page', 1); // Lấy số trang hiện tại
        $totalPages = (int) ceil($workoutDays->count() / $perPage); // Tổng số trang
    
        // Cắt dữ liệu để lấy kết quả của trang hiện tại
        $paginatedWorkouts = $workoutDays->slice(($currentPage - 1) * $perPage, $perPage);
    
        // Lấy số trang trước và trang sau
        $previousPage = $currentPage > 1 ? (int) $currentPage - 1 : null; // Chuyển thành số nguyên
        $nextPage = $currentPage < $totalPages ? (int) $currentPage + 1 : null; // Chuyển thành số nguyên
    
        // Trả về dữ liệu phân trang cùng với các số trang
        $paginatedData = [
            'list' => $paginatedWorkouts->values(),
            'pagination' => [
                'previous_page' => $previousPage,
                'current_page' => $currentPage,
                'next_page' => $nextPage,
                'total_pages' => $totalPages
            ],
        ];
    
        return $this->success($paginatedData, null);
    }    
}
