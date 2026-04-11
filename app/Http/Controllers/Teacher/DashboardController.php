<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\TdQuestionThread;
use App\Models\TdSet;
use App\Models\TeacherAssignment;
use App\Models\TeacherMessage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $teacher = auth()->user();
        $assignments = $this->assignments($teacher->id);
        $courseQuery = Course::query()->with(['subject', 'schoolClass']);
        $this->applyAssignments($courseQuery, $assignments);

        $tdQuery = Schema::hasTable('td_sets')
            ? TdSet::query()->with(['subject', 'schoolClass'])
            : null;
        if ($tdQuery) {
            $this->applyAssignments($tdQuery, $assignments);
        }

        $messageQuery = Schema::hasTable('teacher_messages')
            ? TeacherMessage::query()->where('teacher_id', $teacher->id)->with(['student', 'schoolClass', 'subject'])
            : null;

        $tdQuestionQuery = Schema::hasTable('td_question_threads')
            ? TdQuestionThread::query()->where('teacher_id', $teacher->id)->with(['student', 'tdSet', 'subject'])
            : null;

        return view('teacher.dashboard', [
            'teacher' => $teacher,
            'assignments' => $assignments,
            'stats' => [
                'classes' => $assignments->pluck('school_class_id')->unique()->count(),
                'subjects' => $assignments->pluck('subject_id')->unique()->count(),
                'courses' => (clone $courseQuery)->count(),
                'published_courses' => (clone $courseQuery)->where('status', Course::STATUS_PUBLISHED)->count(),
                'draft_courses' => (clone $courseQuery)->where('status', Course::STATUS_DRAFT)->count(),
                'messages' => $messageQuery ? (clone $messageQuery)->count() : 0,
                'unread_messages' => $messageQuery ? (clone $messageQuery)->where('status', TeacherMessage::STATUS_UNREAD)->count() : 0,
                'td_total' => $tdQuery ? (clone $tdQuery)->count() : 0,
                'td_published' => $tdQuery ? (clone $tdQuery)->where('status', TdSet::STATUS_PUBLISHED)->count() : 0,
                'td_questions_open' => $tdQuestionQuery ? (clone $tdQuestionQuery)->where('status', TdQuestionThread::STATUS_OPEN)->count() : 0,
            ],
            'recentCourses' => (clone $courseQuery)->latest()->take(6)->get(),
            'recentMessages' => $messageQuery ? (clone $messageQuery)->latest()->take(6)->get() : collect(),
            'recentTdSets' => $tdQuery ? (clone $tdQuery)->latest()->take(6)->get() : collect(),
            'recentTdQuestions' => $tdQuestionQuery ? (clone $tdQuestionQuery)->latest('last_message_at')->take(6)->get() : collect(),
        ]);
    }

    protected function assignments(int $teacherId): Collection
    {
        if (!Schema::hasTable('teacher_assignments')) {
            return collect();
        }

        return TeacherAssignment::query()
            ->with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->get();
    }

    protected function applyAssignments($query, Collection $assignments): void
    {
        if ($assignments->isEmpty()) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where(function ($builder) use ($assignments) {
            foreach ($assignments as $assignment) {
                $builder->orWhere(function ($inner) use ($assignment) {
                    $inner->where('school_class_id', $assignment->school_class_id)
                        ->where('subject_id', $assignment->subject_id);
                });
            }
        });
    }
}
