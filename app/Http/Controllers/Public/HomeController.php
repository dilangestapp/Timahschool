<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;

class HomeController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::active()->orderBy('order')->get();

        $classGroups = $classes->groupBy('level');

        $classGroupLabels = [
            'enseignement_general' => 'Enseignement général',
            'enseignement_technique' => 'Enseignement technique',
        ];

        return view('public.home', compact('classes', 'classGroups', 'classGroupLabels'));
    }
}
