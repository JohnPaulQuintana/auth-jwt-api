<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'title',
        'description',
    ];

    // Optional: relation to User
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function exercises()
    {
        return $this->hasMany(ReadingExercise::class, 'lesson_id');
    }

    public function spelling_activities()
    {
        return $this->hasMany(SpellingActivity::class, 'lesson_id');
    }

    public function pictures()
    {
        return $this->hasMany(Picture::class, 'lesson_id');
    }



}
