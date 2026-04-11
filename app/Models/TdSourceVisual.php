<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TdSourceVisual extends Model
{
    use HasFactory;

    public const ROLE_ESSENTIAL = 'essential';
    public const ROLE_USEFUL = 'useful';
    public const ROLE_OPTIONAL = 'optional';

    protected $fillable = [
        'td_source_id',
        'file_path',
        'file_name',
        'file_mime',
        'file_size',
        'exercise_label',
        'visual_role',
        'notes',
        'position',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'position' => 'integer',
    ];

    public function source() { return $this->belongsTo(TdSource::class, 'td_source_id'); }
    public function isImage(): bool { return str_starts_with((string) $this->file_mime, 'image/'); }
    public function humanFileSize(): string
    {
        $bytes = (int) ($this->file_size ?? 0);
        if ($bytes <= 0) {
            return '-';
        }
        $units = ['o', 'Ko', 'Mo', 'Go'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);
        return number_format($value, $power === 0 ? 0 : 2, ',', ' ') . ' ' . $units[$power];
    }
}
