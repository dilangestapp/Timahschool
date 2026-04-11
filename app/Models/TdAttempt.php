<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TdAttempt extends Model
{
    use HasFactory;

    public const STATUS_OPENED = 'opened';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'td_set_id',
        'student_id',
        'status',
        'opened_at',
        'completed_at',
        'submitted_at',
        'correction_unlocked_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'completed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'correction_unlocked_at' => 'datetime',
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
