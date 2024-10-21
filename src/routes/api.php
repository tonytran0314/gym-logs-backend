<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/info', function () {
    return response()->json([
        'name' => 'Trần Gia Huy',
        'country' => 'Việt Nam',
        'car' => 'Vinfast VF9',
        'motorbike' => 'Honda Winner X 2022',
    ]);
})->middleware('auth:sanctum');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        return response()->json([
            'message' => 'Login successful!',
        ], 200);
    } else {
        return response()->json([
            'message' => 'Invalid login credentials!',
        ], 401);
    }
});

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
 
    $request->session()->regenerateToken();

    return response()->json([
        'message' => 'Successfully logged out!',
    ]);
});