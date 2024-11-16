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

        $exercises = $records->pluck('exercise')->unique()->values()->toArray();


        $mostCommonExercise = collect($records)
                                ->pluck('exercise') 
                                ->countBy()         
                                ->sortDesc()        
                                ->keys()            
                                ->first();          

        $exercise = $selectedExercise ?? $mostCommonExercise;
        
        $data = $records->where('exercise', $exercise);
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
            'exercises' => $exercises
        ]);
    }
}
