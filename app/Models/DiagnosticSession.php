<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosticSession extends Model
{
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'user_id',
        'status',
        'current_step',
        'total_questions',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'current_step' => 'integer',
        'total_questions' => 'integer',
    ];

    public function answers()
    {
        return $this->hasMany(DiagnosticAnswer::class, 'diagnostic_session_id');
    }
}
