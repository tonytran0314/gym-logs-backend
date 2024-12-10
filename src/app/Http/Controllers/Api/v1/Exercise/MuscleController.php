<?php

namespace App\Http\Controllers\Api\v1\Exercise;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Muscle;
use App\Traits\HttpResponses;

class MuscleController extends Controller
{
    use HttpResponses;

    public function index() {
        $muscles = Muscle::all();
        return $this->success($muscles, null);
    }
}
