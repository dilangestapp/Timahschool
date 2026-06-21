<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedagogicalSupervisionNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'responsibility_id',
        'author_id',
        'target_user_id',
        'teaching_division_id',
        'teaching_department_id',
        'title',
        'message',
        'severity',
        'status',
        'due_at',
        'resolved_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function responsibility()
    {
        return $this->belongsTo(PedagogicalResponsibility::class, 'responsibility_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function division()
    {
        return $this->belongsTo(TeachingDivision::class, 'teaching_division_id');
    }

    public function department()
    {
        return $this->belongsTo(PedagogicalDepartment::class, 'teaching_department_id');
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'pending'], true);
    }
}
