<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\TeacherAssignment;
use App\Models\TeacherMessage;
use Illuminate\Support\Facades\Schema;

class ClassController extends Controller
{
    public function index()
    {
        $teacherId = auth()->id();

        $assignments = Schema::hasTable('teacher_assignments')
            ? TeacherAssignment::query()
                ->with(['schoolClass', 'subject'])
                ->where('teacher_id', $teacherId)
                ->where('is_active', true)
                ->get()
            : collect();

        $cards = $assignments->map(function ($assignment) use ($teacherId) {
            $courseCount = Course::query()
                ->where('school_class_id', $assignment->school_class_id)
                ->where('subject_id', $assignment->subject_id)
                ->count();

            $unreadMessages = Schema::hasTable('teacher_messages')
                ? TeacherMessage::query()
                    ->where('teacher_id', $teacherId)
                    ->where('school_class_id', $assignment->school_class_id)
                    ->where('subject_id', $assignment->subject_id)
                    ->where('status', TeacherMessage::STATUS_UNREAD)
                    ->count()
                : 0;

            return [
                'assignment' => $assignment,
                'course_count' => $courseCount,
                'unread_messages' => $unreadMessages,
            ];
        });

        return view('teacher.classes.index', compact('cards'));
    }
}
