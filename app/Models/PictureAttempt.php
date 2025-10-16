<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PictureAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'picture_id',
        'attempts',
        'status',
        'type'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pictureActivity()
    {
        return $this->belongsTo(Picture::class, 'picture_id');
    }
}
