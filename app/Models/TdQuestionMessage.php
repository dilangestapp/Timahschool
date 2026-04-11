<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TdQuestionMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'thread_id',
        'sender_id',
        'sender_role',
        'message_html',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function thread()
    {
        return $this->belongsTo(TdQuestionThread::class, 'thread_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function isImageAttachment(): bool
    {
        $mime = strtolower((string) ($this->attachment_mime ?? ''));
        if (str_starts_with($mime, 'image/')) {
            return true;
        }

        $name = strtolower((string) ($this->attachment_name ?? ''));
        return str_ends_with($name, '.jpg') || str_ends_with($name, '.jpeg') || str_ends_with($name, '.png') || str_ends_with($name, '.webp');
    }
}
