<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TdSet extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    public const ACCESS_FREE = 'free';
    public const ACCESS_PREMIUM = 'premium';

    protected $fillable = [
        'school_class_id',
        'subject_id',
        'teacher_assignment_id',
        'author_user_id',
        'title',
        'slug',
        'chapter_label',
        'difficulty',
        'access_level',
        'status',
        'document_path',
        'document_name',
        'document_mime',
        'document_size',
        'editable_html',
        'editable_text',
        'has_editable_version',
        'correction_html',
        'correction_document_path',
        'correction_document_name',
        'correction_document_mime',
        'correction_document_size',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'correction_release_at' => 'datetime',
        'has_editable_version' => 'boolean',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function assignment()
    {
        return $this->belongsTo(TeacherAssignment::class, 'teacher_assignment_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function attempts()
    {
        return $this->hasMany(TdAttempt::class, 'td_set_id');
    }

    public function questionThreads()
    {
        return $this->hasMany(TdQuestionThread::class, 'td_set_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function hasDocument(): bool
    {
        return !empty($this->document_path);
    }

    public function hasCorrectionDocument(): bool
    {
        return !empty($this->correction_document_path);
    }

    public function hasCorrectionContent(): bool
    {
        return !empty($this->correction_html) || $this->hasCorrectionDocument();
    }

    public function correctionIsAvailableFor(?User $user, ?TdAttempt $attempt = null): bool
    {
        if (!$this->hasCorrectionContent()) {
            return false;
        }

        if (!$this->canStudentAccess($user)) {
            return false;
        }

        if (!$attempt || $attempt->status !== TdAttempt::STATUS_COMPLETED) {
            return false;
        }

        if (!$attempt->correction_unlocked_at || now()->lessThan($attempt->correction_unlocked_at)) {
            return false;
        }

        if (($this->correction_mode ?? null) === 'scheduled' && !empty($this->correction_release_at)) {
            return now()->greaterThanOrEqualTo($this->correction_release_at);
        }

        return true;
    }

    public function humanDocumentSize(): string
    {
        return $this->formatBytes((int) ($this->document_size ?? 0));
    }

    public function humanCorrectionDocumentSize(): string
    {
        return $this->formatBytes((int) ($this->correction_document_size ?? 0));
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '-';
        }

        $units = ['o', 'Ko', 'Mo', 'Go'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return number_format($value, $power === 0 ? 0 : 2, ',', ' ') . ' ' . $units[$power];
    }

    public function canStudentAccess(?User $user): bool
    {
        if ($this->access_level === self::ACCESS_FREE) {
            return true;
        }

        return $user?->hasActiveSubscription() ?? false;
    }
}
