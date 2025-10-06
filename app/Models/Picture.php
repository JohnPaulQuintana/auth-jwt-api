<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Picture extends Model
{
    use HasFactory;

    protected $fillable = ['lesson_id', 'title', 'image_path'];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
