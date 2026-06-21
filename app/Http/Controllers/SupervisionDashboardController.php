<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SupervisionDashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('supervision.dashboard', [
            'schemaReady' => false,
            'responsibilities' => collect(),
            'activeResponsibility' => null,
            'areaTitle' => 'Supervision pédagogique',
            'stats' => [],
            'teachers' => collect(),
            'courses' => collect(),
            'tdSets' => collect(),
            'questions' => collect(),
            'notes' => collect(),
        ]);
    }
}
