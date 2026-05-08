<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedagogicalBankItem extends Model
{
    use HasFactory;

    public const TYPE_TD = 'td';
    public const TYPE_COURSE = 'course';
    public const TYPE_QUIZ = 'quiz';
    public const TYPE_EVALUATION = 'evaluation';
    public const TYPE_RESOURCE = 'resource';

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_USED = 'used';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'school_class_id',
        'subject_id',
        'created_by',
        'code',
        'title',
        'content_type',
        'inferred_class',
        'inferred_subject',
        'theme',
        'document_path',
        'document_drive_id',
        'document_drive_url',
        'document_name',
        'document_mime',
        'document_size',
        'correction_document_path',
        'correction_document_drive_id',
        'correction_document_drive_url',
        'correction_document_name',
        'correction_document_mime',
        'correction_document_size',
        'status',
        'times_used',
        'last_scheduled_at',
        'last_td_set_id',
        'notes',
    ];

    protected $casts = [
        'last_scheduled_at' => 'datetime',
        'times_used' => 'integer',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lastTdSet()
    {
        return $this->belongsTo(TdSet::class, 'last_td_set_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_USED => 'Déjà utilisé',
            self::STATUS_ARCHIVED => 'Archivé',
            default => 'Disponible',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->content_type) {
            self::TYPE_COURSE => 'Cours',
            self::TYPE_QUIZ => 'Quiz',
            self::TYPE_EVALUATION => 'Évaluation',
            self::TYPE_RESOURCE => 'Ressource',
            default => 'TD',
        };
    }
}
