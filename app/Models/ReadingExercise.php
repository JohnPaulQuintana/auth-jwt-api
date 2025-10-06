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
        return $this->belongsTo(Lesson::class);
    }
}
