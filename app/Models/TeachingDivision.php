<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeachingDivision extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function departments()
    {
        return $this->hasMany(PedagogicalDepartment::class, 'teaching_division_id')->orderBy('order')->orderBy('name');
    }

    public function responsibilities()
    {
        return $this->hasMany(PedagogicalResponsibility::class, 'teaching_division_id');
    }

    public function supervisionNotes()
    {
        return $this->hasMany(PedagogicalSupervisionNote::class, 'teaching_division_id');
    }

    public function activeDepartments()
    {
        return $this->departments()->where('is_active', true);
    }
}
