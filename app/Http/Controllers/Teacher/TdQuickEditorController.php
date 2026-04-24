<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TdSet;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;

class TdQuickEditorController extends Controller
{
    public function edit(Request $request, TdSet $td)
    {
        $allowed = TeacherAssignment::query()
            ->where('teacher_id', $request->user()->id)
            ->where('school_class_id', $td->school_class_id)
            ->where('subject_id', $td->subject_id)
            ->where('is_active', true)
            ->exists();

        abort_unless($allowed, 403);

        return view('teacher.td.sets.editor', [
            'td' => $td->load(['schoolClass', 'subject', 'assignment']),
        ]);
    }
}
