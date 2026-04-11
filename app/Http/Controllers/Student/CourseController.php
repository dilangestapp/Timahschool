<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Subject;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $profile = auth()->user()->studentProfile;

        abort_unless($profile && $profile->school_class_id, 403, 'Aucune classe élève n\'est liée à ce compte.');

        $query = Course::query()
            ->with(['subject', 'creator'])
            ->where('school_class_id', $profile->school_class_id)
            ->where('status', Course::STATUS_PUBLISHED)
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        $term = trim((string) $request->get('q', ''));
        if ($term !== '') {
            $query->where(function ($builder) use ($term) {
                $builder->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('objectives', 'like', "%{$term}%")
                    ->orWhere('document_name', 'like', "%{$term}%");

                if (\Schema::hasColumn('courses', 'content_text')) {
                    $builder->orWhere('content_text', 'like', "%{$term}%");
                }
            });
        }

        $subjectId = (int) $request->get('subject_id', 0);
        if ($subjectId > 0) {
            $query->where('subject_id', $subjectId);
        }

        $courses = $query->paginate(12)->withQueryString();

        $subjectIds = Course::query()
            ->where('school_class_id', $profile->school_class_id)
            ->where('status', Course::STATUS_PUBLISHED)
            ->pluck('subject_id')
            ->filter()
            ->unique()
            ->values();

        $subjects = Subject::query()
            ->whereIn('id', $subjectIds)
            ->orderBy('name')
            ->get();

        return view('student.courses.index', [
            'courses' => $courses,
            'subjects' => $subjects,
            'filters' => $request->only('q', 'subject_id'),
            'studentProfile' => $profile,
        ]);
    }

    public function show(Course $course)
    {
        $profile = auth()->user()->studentProfile;
        abort_unless($profile && $profile->school_class_id, 403);

        $this->authorizeCourse($course, $profile->school_class_id);

        return view('student.courses.show', [
            'course' => $course->load(['subject', 'creator', 'schoolClass']),
            'studentProfile' => $profile,
        ]);
    }

    public function document(Course $course)
    {
        $profile = auth()->user()->studentProfile;
        abort_unless($profile && $profile->school_class_id, 403);

        $this->authorizeCourse($course, $profile->school_class_id);
        abort_unless($course->document_path, 404);

        $path = storage_path('app/' . $course->document_path);
        abort_unless(file_exists($path), 404);

        return response()->file($path, [
            'Content-Type' => $course->document_mime ?: 'application/octet-stream',
        ]);
    }

    public function downloadDocument(Course $course)
    {
        $profile = auth()->user()->studentProfile;
        abort_unless($profile && $profile->school_class_id, 403);

        $this->authorizeCourse($course, $profile->school_class_id);
        abort_unless($course->document_path, 404);

        $path = storage_path('app/' . $course->document_path);
        abort_unless(file_exists($path), 404);

        return response()->download($path, $course->document_name ?: basename($path));
    }

    protected function authorizeCourse(Course $course, int $schoolClassId): void
    {
        abort_unless(
            (int) $course->school_class_id === $schoolClassId
            && $course->status === Course::STATUS_PUBLISHED,
            404
        );
    }
}
