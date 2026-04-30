<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentLearningProfile extends Model
{
    protected $fillable = [
        'user_id',
        'main_goal',
        'target_exam',
        'weak_subjects',
        'strong_subjects',
        'preferred_learning_style',
        'weekly_availability',
        'confidence_scores',
        'generated_summary',
        'teacher_notes',
        'diagnostic_completed_at',
    ];

    protected $casts = [
        'weak_subjects' => 'array',
        'strong_subjects' => 'array',
        'confidence_scores' => 'array',
        'diagnostic_completed_at' => 'datetime',
    ];
}
