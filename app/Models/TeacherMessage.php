<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherMessage extends Model
{
    use HasFactory;

    const STATUS_UNREAD = 'unread';
    const STATUS_READ = 'read';
    const STATUS_REPLIED = 'replied';

    protected $fillable = [
        'teacher_assignment_id',
        'teacher_id',
        'student_id',
        'school_class_id',
        'subject_id',
        'topic',
        'title',
        'message',
        'attachment_path',
        'attachment_name',
        'status',
        'read_at',
        'reply_message',
        'replied_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
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

    public function scopeUnread($query)
    {
        return $query->where('status', self::STATUS_UNREAD);
    }

    public function getDisplayTitleAttribute(): string
    {
        return (string) ($this->title ?: $this->topic ?: 'Sans objet');
    }

    public function attachmentExtension(): ?string
    {
        $name = $this->attachment_name ?: $this->attachment_path;

        if (!$name) {
            return null;
        }

        return strtolower(pathinfo($name, PATHINFO_EXTENSION));
    }

    public function isImageAttachment(): bool
    {
        $extension = $this->attachmentExtension();

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);
    }

    public function isAudioAttachment(): bool
    {
        $extension = $this->attachmentExtension();

        return in_array($extension, ['mp3', 'wav', 'ogg', 'm4a', 'webm', 'aac', '3gp', 'amr', 'mp4'], true);
    }
}
