<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;

class HomeController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::query()
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $classGroups = $classes->groupBy(function ($class) {
            $system = $class->system ?? null;

            return $system === 'enseignement_technique'
                ? 'enseignement_technique'
                : 'enseignement_general';
        });

        $classGroupLabels = [
            'enseignement_general' => 'Enseignement général',
            'enseignement_technique' => 'Enseignement technique',
        ];

        return view('public.home', compact('classes', 'classGroups', 'classGroupLabels'));
    }
}
