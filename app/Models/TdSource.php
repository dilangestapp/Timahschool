<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TdSource extends Model
{
    use HasFactory;

    public const STATUS_IMPORTED = 'imported';
    public const STATUS_PREPARED = 'prepared';
    public const STATUS_TRANSFORMED = 'transformed';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'teacher_assignment_id',
        'uploaded_by',
        'source_kind',
        'title',
        'source_url',
        'source_label',
        'raw_text',
        'extracted_text',
        'source_file_path',
        'source_file_name',
        'source_file_mime',
        'source_file_size',
        'detected_school_class_id',
        'detected_subject_id',
        'detected_chapter_label',
        'detected_difficulty',
        'detected_structure_json',
        'analysis_notes',
        'prompt_ready_text',
        'prompt_package_json',
        'prepared_at',
        'status',
        'rights_confirmed',
    ];

    protected $casts = [
        'detected_structure_json' => 'array',
        'prompt_package_json' => 'array',
        'rights_confirmed' => 'boolean',
        'source_file_size' => 'integer',
        'prepared_at' => 'datetime',
    ];

    public function teacherAssignment() { return $this->belongsTo(TeacherAssignment::class, 'teacher_assignment_id'); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function detectedSchoolClass() { return $this->belongsTo(SchoolClass::class, 'detected_school_class_id'); }
    public function detectedSubject() { return $this->belongsTo(Subject::class, 'detected_subject_id'); }
    public function transformations() { return $this->hasMany(TdTransformation::class, 'td_source_id')->latest(); }
    public function tdSets() { return $this->hasMany(TdSet::class, 'td_source_id')->latest(); }
    public function visuals() { return $this->hasMany(TdSourceVisual::class, 'td_source_id')->orderBy('position'); }

    public function getWorkingTextAttribute(): string
    {
        return trim(implode("\n\n", array_filter([
            (string) $this->title,
            (string) $this->raw_text,
            (string) $this->extracted_text,
        ])));
    }

    public function humanFileSize(): string
    {
        $bytes = (int) ($this->source_file_size ?? 0);
        if ($bytes <= 0) {
            return '-';
        }
        $units = ['o', 'Ko', 'Mo', 'Go'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);
        return number_format($value, $power === 0 ? 0 : 2, ',', ' ') . ' ' . $units[$power];
    }
}
