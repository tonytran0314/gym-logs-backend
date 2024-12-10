<?php

namespace App\Http\Controllers\Api\v1\Exercise;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Muscle;
use App\Traits\HttpResponses;

class ExerciseController extends Controller
{
    use HttpResponses;

    public function index(Request $request) {
        $muscleID = $request->muscle_id;
        $exercises = Muscle::find($muscleID)->exercises()->get();
        return $this->success($exercises, null);
    }
}
