<?php

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userID = Auth::user()->id;
        
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'email', 
                'string', 
                'max:255', 
                Rule::unique('users')->ignore($userID)
            ],
        ];
    }
}
