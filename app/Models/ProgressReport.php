<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'school_class_id',
        'biweekly_evaluation_id',
        'period_starts_at',
        'period_ends_at',
        'participation_rate',
        'evaluation_score',
        'courses_done',
        'td_done',
        'quizzes_done',
        'strengths',
        'weaknesses',
        'recommendations',
        'status',
        'published_at',
    ];

    protected $casts = [
        'period_starts_at' => 'datetime',
        'period_ends_at' => 'datetime',
        'published_at' => 'datetime',
        'evaluation_score' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function evaluation()
    {
        return $this->belongsTo(BiweeklyEvaluation::class, 'biweekly_evaluation_id');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at !== null;
    }
}
