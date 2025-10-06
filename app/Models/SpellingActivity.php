<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpellingActivity extends Model
{
    use HasFactory;

    protected $fillable = ['lesson_id', 'word', 'image', 'missing_letter_indexes'];
    protected $casts = [
        'missing_letter_indexes' => 'array',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }


}
