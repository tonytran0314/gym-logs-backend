<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/info', function () {
    return response()->json([
        'name' => 'Trần Gia Huy',
        'country' => 'Việt Nam',
        'car' => 'Vinfast VF9',
        'motorbike' => 'Honda Winner X 2022',
    ]);
});
