<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadingAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reading_exercise_id',
        'attempts',
        'status',
        'type'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function readingActivity()
    {
        return $this->belongsTo(ReadingExercise::class, 'reading_exercise_id');
    }
}
