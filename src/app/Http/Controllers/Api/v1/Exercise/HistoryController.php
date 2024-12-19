<?php
namespace App\Http\Controllers\Api\v1\Exercise;

use App\Http\Controllers\Controller;
use App\Models\ExerciseRecords;
use Illuminate\Support\Facades\Auth;
use App\Models\Exercise;
use App\Models\Muscle;
use App\Traits\HttpResponses;

class HistoryController extends Controller
{
    use HttpResponses;
   
    public function index() {
        $userID = Auth::user()->id;
    
        $records = ExerciseRecords::where('user_id', $userID)
            ->orderByDesc('created_at')
            ->get();
    
        // Check if there are any records
        if ($records->isEmpty()) {
            return $this->success(null, 'The workout days history is currently empty. Please start working out');
        }
    
        // Group records by date
        $groupedRecords = $records->groupBy(function ($item) {
            return $item->created_at->format('D, M d'); // Group by day (e.g., "Tue, Dec 19")
        });
    
        // Convert the grouped records to a paginated array of workout days
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
    
        // Paginate the workout days (after grouping by date)
        $perPage = 5; // Set the number of workout days per page
        $currentPage = (int) request()->get('page', 1); // Get the current page as an integer
        $totalPages = (int) ceil($workoutDays->count() / $perPage); // Total pages as an integer
    
        // Slice the workout days to get the current page of results
        $paginatedWorkouts = $workoutDays->slice(($currentPage - 1) * $perPage, $perPage);
    
        // Get the previous and next page numbers
        $previousPage = $currentPage > 1 ? (int) $currentPage - 1 : null; // Convert to integer
        $nextPage = $currentPage < $totalPages ? (int) $currentPage + 1 : null; // Convert to integer
    
        // Return paginated data with page numbers
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
