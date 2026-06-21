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
        return $this->hasMany(TeachingDepartment::class)->orderBy('order')->orderBy('name');
    }

    public function responsibilities()
    {
        return $this->hasMany(PedagogicalResponsibility::class);
    }

    public function supervisionNotes()
    {
        return $this->hasMany(PedagogicalSupervisionNote::class);
    }

    public function activeDepartments()
    {
        return $this->departments()->where('is_active', true);
    }
}
