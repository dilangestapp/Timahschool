<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileQuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mobile_quiz_id',
        'answers',
        'score',
        'correct_count',
        'total_questions',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'score' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quiz()
    {
        return $this->belongsTo(MobileQuiz::class, 'mobile_quiz_id');
    }
}
