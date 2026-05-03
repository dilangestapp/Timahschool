<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileQuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'mobile_quiz_id',
        'question',
        'type',
        'choices',
        'correct_answer',
        'explanation',
        'points',
        'position',
    ];

    protected $casts = [
        'choices' => 'array',
        'points' => 'integer',
        'position' => 'integer',
    ];

    public function quiz()
    {
        return $this->belongsTo(MobileQuiz::class, 'mobile_quiz_id');
    }
}
