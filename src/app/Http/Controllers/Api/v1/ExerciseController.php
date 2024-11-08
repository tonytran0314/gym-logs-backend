<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExerciseController extends Controller
{
    public function isWorkingout() {
        
        if (Auth::user()->isWorkingout) {
            return response()->json([
                'isWorkingout' => true,
            ]); 
        }

        return response()->json([
            'isWorkingout' => false,
        ]); 
    }

    public function toggleWorkout() {
        Auth::user()->isWorkingout = !Auth::user()->isWorkingout;
    }
}
