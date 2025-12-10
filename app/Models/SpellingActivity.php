<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpellingActivity extends Model
{
    use HasFactory;

    protected $fillable = ['lesson_id', 'word', 'image', 'missing_letter_indexes', 'descriptions'];
    protected $casts = [
        'missing_letter_indexes' => 'array',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    //has many user attempts
    public function attempts()
    {
        return $this->hasMany(SpellingAttempt::class, 'spelling_activity_id');
    }

    //has many users through attempts
    public function users()
    {
        return $this->belongsToMany(User::class, 'spelling_attempts');
    }
}
