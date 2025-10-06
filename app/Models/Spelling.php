<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spelling extends Model
{
    use HasFactory;

    protected $table = 'spelling_table';

    protected $fillable = [
        'teacher_id',
        'title',
        'description',
        'icon',
        'attempts',
        'letters_to_remove',
        'score',
    ];

    protected $casts = [
        'letters_to_remove' => 'array', // automatically convert JSON to array
    ];
}
