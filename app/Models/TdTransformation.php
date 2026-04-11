<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TdTransformation extends Model
{
    use HasFactory;

    public const STATUS_PREPARED = 'prepared';
    public const STATUS_IMPORTED = 'imported';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'td_source_id',
        'author_user_id',
        'variant_type',
        'generation_notes',
        'prompt_snapshot',
        'generated_title',
        'generated_summary',
        'generated_instructions_html',
        'generated_correction_html',
        'generated_structure_json',
        'status',
        'td_set_id',
    ];

    protected $casts = [
        'generated_structure_json' => 'array',
    ];

    public function source() { return $this->belongsTo(TdSource::class, 'td_source_id'); }
    public function author() { return $this->belongsTo(User::class, 'author_user_id'); }
    public function tdSet() { return $this->belongsTo(TdSet::class, 'td_set_id'); }
}
