<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpellingAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'spelling_activity_id',
        'attempts',
        'status',
        'type'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function spellingActivity()
    {
        return $this->belongsTo(SpellingActivity::class, 'spelling_activity_id');
    }
}
