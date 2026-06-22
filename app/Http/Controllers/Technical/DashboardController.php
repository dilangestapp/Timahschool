<?php

namespace App\Http\Controllers\Technical;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\TdAttempt;
use App\Models\TdSet;
use App\Models\TeacherAssignment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $technicalClasses = $this->technicalClasses();
        $classIds = $technicalClasses->pluck('id')->values();
        $assignments = $this->technicalAssignments($classIds);
        $teacherIds = $assignments->pluck('teacher_id')->filter()->unique()->values();
        $assignmentIds = $assignments->pluck('id')->filter()->values();

        $courses = $this->technicalCourses($classIds);
        $tdSets = $this->technicalTdSets($classIds);
        $attempts = $this->technicalAttempts($tdSets->pluck('id')->values());

        $teacherCourseCounts = $courses->groupBy('created_by')->map->count();
        $teacherTdCounts = $tdSets->groupBy('author_user_id')->map->count();

        $teacherRows = $assignments
            ->groupBy('teacher_id')
            ->map(function ($items, $teacherId) use ($teacherCourseCounts, $teacherTdCounts) {
                $teacher = $items->first()->teacher ?? null;

                return [
                    'id' => (int) $teacherId,
                    'name' => $teacher->full_name ?? $teacher->name ?? $teacher->username ?? 'Enseignant non defini',
                    'status' => $teacher->status ?? 'active',
                    'subjects' => $items->pluck('subject.name')->filter()->unique()->values(),
                    'classes' => $items->pluck('schoolClass.name')->filter()->unique()->values(),
                    'courses' => (int) ($teacherCourseCounts[$teacherId] ?? 0),
                    'td' => (int) ($teacherTdCounts[$teacherId] ?? 0),
                ];
            })
            ->values();

        $classRows = $technicalClasses->map(function ($class) use ($courses, $tdSets, $assignments) {
            $classAssignments = $assignments->where('school_class_id', $class->id);

            return [
                'id' => $class->id,
                'name' => $class->name,
                'level' => $class->level ?? 'enseignement_technique',
                'students' => (int) ($class->student_profiles_count ?? 0),
                'teachers' => $classAssignments->pluck('teacher_id')->filter()->unique()->count(),
                'subjects' => $classAssignments->pluck('subject.name')->filter()->unique()->values(),
                'published_courses' => $courses->where('school_class_id', $class->id)->where('status', Course::STATUS_PUBLISHED)->count(),
                'td' => $tdSets->where('school_class_id', $class->id)->count(),
            ];
        });

        $stats = [
            'classes' => $technicalClasses->count(),
            'teachers' => $teacherIds->count(),
            'students' => $this->countStudents($classIds),
            'subjects' => $assignments->pluck('subject_id')->filter()->unique()->count(),
            'courses' => $courses->count(),
            'published_courses' => $courses->where('status', Course::STATUS_PUBLISHED)->count(),
            'draft_courses' => $courses->where('status', Course::STATUS_DRAFT)->count(),
            'td' => $tdSets->count(),
            'published_td' => $tdSets->where('status', TdSet::STATUS_PUBLISHED)->count(),
            'attempts' => $attempts->count(),
            'submitted_attempts' => $attempts->whereIn('status', [TdAttempt::STATUS_SUBMITTED, TdAttempt::STATUS_COMPLETED, TdAttempt::STATUS_CORRECTED, TdAttempt::STATUS_GRADED])->count(),
        ];

        $alerts = $this->buildAlerts($technicalClasses, $assignments, $courses, $tdSets, $teacherRows);

        return view('technical.dashboard', [
            'stats' => $stats,
            'technicalClasses' => $technicalClasses,
            'classRows' => $classRows,
            'teacherRows' => $teacherRows,
            'recentCourses' => $courses->sortByDesc('updated_at')->take(8)->values(),
            'recentTds' => $tdSets->sortByDesc('updated_at')->take(8)->values(),
            'alerts' => $alerts,
            'courseStatusCounts' => $courses->groupBy('status')->map->count(),
            'tdStatusCounts' => $tdSets->groupBy('status')->map->count(),
        ]);
    }

    protected function technicalClasses(): Collection
    {
        if (!Schema::hasTable('school_classes')) {
            return collect();
        }

        $query = SchoolClass::query()
            ->when(Schema::hasTable('student_profiles'), fn ($q) => $q->withCount('studentProfiles'))
            ->orderBy('order')
            ->orderBy('name');

        if (Schema::hasColumn('school_classes', 'level')) {
            $query->where('level', 'enseignement_technique');
        }

        return $query->get();
    }

    protected function technicalAssignments(Collection $classIds): Collection
    {
        if ($classIds->isEmpty() || !Schema::hasTable('teacher_assignments')) {
            return collect();
        }

        return TeacherAssignment::query()
            ->with(['teacher', 'schoolClass', 'subject'])
            ->whereIn('school_class_id', $classIds)
            ->when(Schema::hasColumn('teacher_assignments', 'is_active'), fn ($q) => $q->where('is_active', true))
            ->get();
    }

    protected function technicalCourses(Collection $classIds): Collection
    {
        if ($classIds->isEmpty() || !Schema::hasTable('courses')) {
            return collect();
        }

        return Course::query()
            ->with(['schoolClass', 'subject', 'creator'])
            ->whereIn('school_class_id', $classIds)
            ->get();
    }

    protected function technicalTdSets(Collection $classIds): Collection
    {
        if ($classIds->isEmpty() || !Schema::hasTable('td_sets')) {
            return collect();
        }

        return TdSet::query()
            ->with(['schoolClass', 'subject', 'author'])
            ->whereIn('school_class_id', $classIds)
            ->get();
    }

    protected function technicalAttempts(Collection $tdIds): Collection
    {
        if ($tdIds->isEmpty() || !Schema::hasTable('td_attempts')) {
            return collect();
        }

        return DB::table('td_attempts')->whereIn('td_set_id', $tdIds)->get();
    }

    protected function countStudents(Collection $classIds): int
    {
        if ($classIds->isEmpty() || !Schema::hasTable('student_profiles')) {
            return 0;
        }

        return (int) StudentProfile::query()->whereIn('school_class_id', $classIds)->count();
    }

    protected function buildAlerts(Collection $classes, Collection $assignments, Collection $courses, Collection $tdSets, Collection $teacherRows): Collection
    {
        $alerts = collect();

        if ($classes->isEmpty()) {
            $alerts->push([
                'level' => 'warning',
                'title' => 'Aucune classe technique configuree',
                'message' => 'Ajoutez des classes avec le niveau Enseignement technique dans le backoffice pour alimenter ce tableau de bord.',
            ]);

            return $alerts;
        }

        foreach ($classes as $class) {
            $classAssignments = $assignments->where('school_class_id', $class->id);
            $publishedCourses = $courses->where('school_class_id', $class->id)->where('status', Course::STATUS_PUBLISHED)->count();
            $publishedTd = $tdSets->where('school_class_id', $class->id)->where('status', TdSet::STATUS_PUBLISHED)->count();

            if ($classAssignments->isEmpty()) {
                $alerts->push([
                    'level' => 'danger',
                    'title' => $class->name . ' sans enseignant actif',
                    'message' => 'Cette classe technique doit recevoir au moins une affectation enseignant active.',
                ]);
            }

            if ($publishedCourses === 0) {
                $alerts->push([
                    'level' => 'warning',
                    'title' => $class->name . ' sans cours publie',
                    'message' => 'Aucun cours publie n est visible pour cette classe technique.',
                ]);
            }

            if ($publishedTd === 0) {
                $alerts->push([
                    'level' => 'info',
                    'title' => $class->name . ' sans TD publie',
                    'message' => 'Aucun TD publie n est actuellement disponible pour cette classe.',
                ]);
            }
        }

        foreach ($teacherRows as $teacher) {
            if (($teacher['status'] ?? 'active') !== 'active') {
                $alerts->push([
                    'level' => 'danger',
                    'title' => $teacher['name'] . ' inactif',
                    'message' => 'Ce compte enseignant est rattache a la section technique mais son statut n est pas actif.',
                ]);
            }

            if (($teacher['courses'] ?? 0) === 0) {
                $alerts->push([
                    'level' => 'warning',
                    'title' => $teacher['name'] . ' sans cours',
                    'message' => 'Aucun cours technique n est associe a cet enseignant.',
                ]);
            }
        }

        $draftCourses = $courses->where('status', Course::STATUS_DRAFT)->count();
        if ($draftCourses > 0) {
            $alerts->push([
                'level' => 'info',
                'title' => $draftCourses . ' cours en brouillon',
                'message' => 'Des contenus techniques doivent encore etre publies ou finalises.',
            ]);
        }

        return $alerts->take(12)->values();
    }
}
