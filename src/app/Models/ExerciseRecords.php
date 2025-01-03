<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ExerciseRecords extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'muscle_id',
        'exercise_id',
        'weight_level',
        'reps',
        'set_number',
        'created_at',
        'updated_at'
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function exercise(): BelongsTo {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }
}
