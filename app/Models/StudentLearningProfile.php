<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentLearningProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_class_id',
        'school_level',
        'main_goal',
        'target_exam',
        'weak_subjects',
        'strong_subjects',
        'study_times',
        'preferred_study_time',
        'preferred_learning_style',
        'weekly_availability',
        'confidence_scores',
        'parent_name',
        'parent_phone',
        'recommendation_title',
        'recommendation_message',
        'recommended_actions',
        'generated_summary',
        'teacher_notes',
        'diagnostic_completed_at',
        'completed_at',
    ];

    protected $casts = [
        'weak_subjects' => 'array',
        'strong_subjects' => 'array',
        'study_times' => 'array',
        'recommended_actions' => 'array',
        'confidence_scores' => 'array',
        'diagnostic_completed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }
}
