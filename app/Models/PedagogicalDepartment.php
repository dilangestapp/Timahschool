<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedagogicalDepartment extends Model
{
    use HasFactory;

    protected $table = 'teaching_departments';

    protected $fillable = ['teaching_division_id', 'subject_id', 'school_class_id', 'name', 'slug', 'code', 'description', 'order', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'order' => 'integer'];
}
