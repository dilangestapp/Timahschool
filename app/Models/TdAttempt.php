<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TdAttempt extends Model
{
    use HasFactory;

    public const STATUS_OPENED = 'opened';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_EXPIRED = 'expired_not_submitted';
    public const STATUS_MISSED = 'missed';
    public const STATUS_CORRECTED = 'corrected';
    public const STATUS_GRADED = 'graded';
    public const STATUS_MAKEUP_REQUESTED = 'makeup_requested';
    public const STATUS_MAKEUP_ACCEPTED = 'makeup_accepted';
    public const STATUS_MAKEUP_REFUSED = 'makeup_refused';

    protected $fillable = [
        'td_set_id',
        'student_id',
        'status',
        'opened_at',
        'submission_deadline_at',
        'completed_at',
        'submitted_at',
        'expired_at',
        'missed_at',
        'correction_unlocked_at',
        'submitted_document_path',
        'submitted_document_name',
        'submitted_document_mime',
        'submitted_document_size',
        'makeup_status',
        'makeup_reason',
        'makeup_requested_at',
        'makeup_decided_at',
        'score',
        'max_score',
        'teacher_feedback',
        'correction_grid',
        'annotations',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'submission_deadline_at' => 'datetime',
        'completed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'expired_at' => 'datetime',
        'missed_at' => 'datetime',
        'correction_unlocked_at' => 'datetime',
        'makeup_requested_at' => 'datetime',
        'makeup_decided_at' => 'datetime',
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'correction_grid' => 'array',
        'annotations' => 'array',
    ];

    public function tdSet()
    {
        return $this->belongsTo(TdSet::class, 'td_set_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
