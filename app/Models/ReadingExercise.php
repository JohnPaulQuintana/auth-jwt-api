<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadingExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'text',
        'difficulty',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    //has many user attempts
    public function attempts()
    {
        return $this->hasMany(ReadingAttempt::class, 'reading_exercise_id');
    }

    //has many users through attempts
    public function users()
    {
        return $this->belongsToMany(User::class, 'spelling_attempts');
    }
}
