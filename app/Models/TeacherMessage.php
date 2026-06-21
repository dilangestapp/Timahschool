<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class TeacherMessage extends Model
{
    use HasFactory;

    const STATUS_UNREAD = 'unread';
    const STATUS_READ = 'read';
    const STATUS_REPLIED = 'replied';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';

    const DIRECTION_STUDENT = 'student';
    const DIRECTION_TEACHER = 'teacher';

    protected $fillable = [
        'teacher_assignment_id', 'teacher_id', 'student_id', 'school_class_id', 'subject_id',
        'topic', 'title', 'message', 'direction', 'parent_message_id',
        'attachment_path', 'attachment_name', 'attachment_mime', 'attachment_size',
        'status', 'delivered_at', 'read_at', 'reply_message', 'replied_at',
        'deleted_by_teacher_at', 'deleted_by_student_at',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
        'deleted_by_teacher_at' => 'datetime',
        'deleted_by_student_at' => 'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(TeacherAssignment::class, 'teacher_assignment_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function parentMessage()
    {
        return $this->belongsTo(self::class, 'parent_message_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('status', self::STATUS_UNREAD);
    }

    public function getDisplayTitleAttribute(): string
    {
        return (string) ($this->title ?: $this->topic ?: 'Sans objet');
    }

    public function isFromTeacher(): bool
    {
        return (string) ($this->direction ?? self::DIRECTION_STUDENT) === self::DIRECTION_TEACHER;
    }

    public function attachmentExtension(): ?string
    {
        $name = $this->attachment_name ?: $this->attachment_path;
        return $name ? strtolower(pathinfo($name, PATHINFO_EXTENSION)) : null;
    }

    public function isImageAttachment(): bool
    {
        return in_array($this->attachmentExtension(), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    }

    public function isAudioAttachment(): bool
    {
        return in_array($this->attachmentExtension(), ['mp3', 'wav', 'ogg', 'm4a', 'webm', 'aac', '3gp', 'amr', 'mp4'], true);
    }

    public function isDocumentAttachment(): bool
    {
        return (bool) $this->attachment_path && !$this->isImageAttachment() && !$this->isAudioAttachment();
    }

    public function humanAttachmentSize(): string
    {
        $size = (int) ($this->attachment_size ?? 0);
        if ($size <= 0) return '';
        if ($size >= 1048576) return number_format($size / 1048576, 1, ',', ' ') . ' Mo';
        return number_format($size / 1024, 0, ',', ' ') . ' Ko';
    }

    public function isAnonymousAudioAttachment(): bool
    {
        if (!$this->isAudioAttachment()) return false;
        $name = strtolower((string) ($this->attachment_name ?: ''));
        return str_contains($name, 'voice-anonyme') || str_contains($name, 'anonymous') || str_contains($name, 'anonyme');
    }

    public static function supportsColumn(string $column): bool
    {
        return Schema::hasColumn('teacher_messages', $column);
    }
}
