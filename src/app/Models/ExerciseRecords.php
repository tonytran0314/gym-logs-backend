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
        'muscle',
        'exercise',
        'weight_level',
        'reps',
        'set_number'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}