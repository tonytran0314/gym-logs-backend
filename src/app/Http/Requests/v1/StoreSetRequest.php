<?php

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StoreSetRequest extends FormRequest
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
        return [
            'muscle_id' => ['required', 'integer', 'exists:muscles,id'],
            'exercise_id' => ['required', 'integer', 'exists:exercises,id'],
            'weight_level' => ['required', 'regex:/^\d+(\.\d)?$/', 'gt:0', 'lt:5000'],
            'reps' => ['required', 'integer', 'gt:0', 'lt:1000'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $exerciseId = $this->input('exercise_id');
            $muscleId = $this->input('muscle_id');

            // Check if the exercise belongs to the muscle
            $isValid = DB::table('exercises')
                ->where('id', $exerciseId)
                ->where('muscle_id', $muscleId)
                ->exists();

            if (!$isValid) {
                $validator->errors()->add('exercise_id', 'The selected exercise does not belong to the specified muscle.');
            }
        });
    }
}
