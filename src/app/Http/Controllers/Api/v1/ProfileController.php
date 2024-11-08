<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProfileController extends Controller
{
    public function profile() {
        return Auth::user();
    }

    public function editProfile(UpdateProfileRequest $request) {

        $userID = Auth::user()->id;
        $user = User::find($userID);
        if(!$user) {
            return response()->json([
                'message' => 'User Not Found'
            ], 404);
        }


        $updated = $user->update([
            'name' => $request->name,
            'email' => $request->email
        ]);
        if(!$updated) {
            return response()->json([
                'message' => 'Failed to Update Profile'
            ], 500);
        }
        

        return response()->json([
            'message' => 'Updated Profile Successfully'
        ], 200);
        
    }
}
