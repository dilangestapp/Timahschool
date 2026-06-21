<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedagogicalResponsibility extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role_title',
        'scope_type',
        'teaching_division_id',
        'teaching_department_id',
        'can_view_reports',
        'can_send_alerts',
        'can_validate_content',
        'is_active',
        'notes',
        'assigned_at',
    ];

    protected $casts = [
        'can_view_reports' => 'boolean',
        'can_send_alerts' => 'boolean',
        'can_validate_content' => 'boolean',
        'is_active' => 'boolean',
        'assigned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function division()
    {
        return $this->belongsTo(TeachingDivision::class, 'teaching_division_id');
    }

    public function department()
    {
        return $this->belongsTo(PedagogicalDepartment::class, 'teaching_department_id');
    }

    public function notes()
    {
        return $this->hasMany(PedagogicalSupervisionNote::class, 'responsibility_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePlatform($query)
    {
        return $query->where('scope_type', 'platform');
    }

    public function scopeDivision($query)
    {
        return $query->where('scope_type', 'division');
    }

    public function scopeDepartment($query)
    {
        return $query->where('scope_type', 'department');
    }

    public function label(): string
    {
        if ($this->scope_type === 'division') {
            return $this->division->name ?? 'Type d’enseignement';
        }

        if ($this->scope_type === 'department') {
            return $this->department->name ?? 'Département';
        }

        return 'Plateforme entière';
    }
}
