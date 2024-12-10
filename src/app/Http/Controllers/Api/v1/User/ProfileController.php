<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Resources\v1\UserResource;
use App\Traits\HttpResponses;

class ProfileController extends Controller
{
    use HttpResponses;

    public function show() {
        return $this->success(new UserResource(Auth::user()), null);
    }

    public function update(UpdateProfileRequest $request) {
        $userID = Auth::user()->id;
        $user = User::find($userID);
        if(!$user) {
            return $this->error(null, 'User Not Found', 404);
        }


        $updated = $user->update([
            'name' => $request->name,
            'email' => $request->email
        ]);
        if(!$updated) {
            return $this->error(null, 'Failed to Update Profile', 500);
        }
        

        return $this->success(new UserResource($user), 'Updated Profile Successfully', 200);
        
    }
}
