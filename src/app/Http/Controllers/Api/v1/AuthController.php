<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request) {
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
    }

    public function logout(Request $request) {
        Auth::logout();

        $request->session()->invalidate();
    
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Successfully logged out!',
        ]);
    }
}
