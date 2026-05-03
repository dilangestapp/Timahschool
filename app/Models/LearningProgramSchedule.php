<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningProgramSchedule extends Model
{
    use HasFactory;

    public const TYPE_COURSE = 'course';
    public const TYPE_TD = 'td';
    public const TYPE_QUIZ = 'quiz';
    public const TYPE_EVALUATION = 'evaluation';

    protected $fillable = [
        'title',
        'description',
        'school_class_id',
        'subject_id',
        'activity_type',
        'week_number',
        'weekday',
        'unlock_time',
        'unlocks_at',
        'closes_at',
        'duration_minutes',
        'status',
        'requires_subscription',
        'admin_note',
    ];

    protected $casts = [
        'unlocks_at' => 'datetime',
        'closes_at' => 'datetime',
        'requires_subscription' => 'boolean',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function isUnlocked(): bool
    {
        return $this->unlocks_at === null || $this->unlocks_at->isPast();
    }
}
