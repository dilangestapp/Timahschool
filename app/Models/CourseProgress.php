<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseProgress extends Model
{
    use HasFactory;

    public const STATUS_NOT_STARTED = 'not_started';
    public const STATUS_OPENED = 'opened';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    protected $table = 'course_progress';

    protected $fillable = [
        'course_id',
        'student_id',
        'status',
        'progress_percent',
        'opened_at',
        'last_seen_at',
        'completed_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress_percent' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
