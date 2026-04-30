<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosticAnswer extends Model
{
    protected $fillable = [
        'diagnostic_session_id',
        'user_id',
        'diagnostic_question_id',
        'category',
        'question_text',
        'answer_text',
        'answer_score',
        'answer_payload',
    ];

    protected $casts = [
        'answer_payload' => 'array',
        'answer_score' => 'integer',
    ];
}
