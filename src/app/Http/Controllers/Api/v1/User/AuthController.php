<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\v1\LoginRequest;
use App\Http\Requests\v1\SignupRequest;
use App\Models\User;
use App\Traits\HttpResponses;

class AuthController extends Controller
{

    use HttpResponses;

    public function login(LoginRequest $request) {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();
            
            return $this->success(null, 'Login successful!');
        } else {
            return $this->error(null, 'Wrong email or password.', 401);
        }
    }

    public function logout(Request $request) {
        Auth::logout();

        $request->session()->invalidate();
    
        $request->session()->regenerateToken();

        return $this->success(null, 'Successfully logged out!');
    }

    public function signup(SignupRequest $request) {
        $record = $request->all();

        $newUser = User::create($record);

        if($newUser) {
            return $this->success(null, 'User created successfully');
        }

        return $this->error(null, 'Failed to create user', 500);
    }

    public function isAuthenticated() {
        if (Auth::check()) {
            return response()->json([
                'isAuthenticated' => true,
            ]); 
        }

        return response()->json([
            'isAuthenticated' => false,
        ]); 
    }
}
