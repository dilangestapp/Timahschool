<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\TdQuestionThread;
use App\Models\TdSet;
use App\Models\TeacherAssignment;
use App\Models\TeacherMessage;
use App\Models\TeacherWeeklyProgram;
use Illuminate\Support\Facades\DB;
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

            $publishedCourses = Course::query()
                ->where('school_class_id', $assignment->school_class_id)
                ->where('subject_id', $assignment->subject_id)
                ->where('status', Course::STATUS_PUBLISHED)
                ->count();

            $tdCount = Schema::hasTable('td_sets')
                ? TdSet::query()->where('school_class_id', $assignment->school_class_id)->where('subject_id', $assignment->subject_id)->count()
                : 0;

            $openQuestions = Schema::hasTable('td_question_threads')
                ? TdQuestionThread::query()->where('teacher_id', $teacherId)->where('school_class_id', $assignment->school_class_id)->where('subject_id', $assignment->subject_id)->where('status', TdQuestionThread::STATUS_OPEN)->count()
                : 0;

            $unreadMessages = Schema::hasTable('teacher_messages')
                ? TeacherMessage::query()->where('teacher_id', $teacherId)->where('school_class_id', $assignment->school_class_id)->where('subject_id', $assignment->subject_id)->where('status', TeacherMessage::STATUS_UNREAD)->count()
                : 0;

            $students = Schema::hasTable('student_profiles')
                ? DB::table('student_profiles')->where('school_class_id', $assignment->school_class_id)->count()
                : 0;

            $weekProgram = Schema::hasTable('teacher_weekly_programs')
                ? TeacherWeeklyProgram::query()->where('teacher_id', $teacherId)->where('school_class_id', $assignment->school_class_id)->where('subject_id', $assignment->subject_id)->whereBetween('program_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])->count()
                : 0;

            return [
                'assignment' => $assignment,
                'course_count' => $courseCount,
                'published_courses' => $publishedCourses,
                'td_count' => $tdCount,
                'open_questions' => $openQuestions,
                'unread_messages' => $unreadMessages,
                'students' => $students,
                'week_program' => $weekProgram,
            ];
        });

        return view('teacher.classes.index', compact('cards'));
    }
}
