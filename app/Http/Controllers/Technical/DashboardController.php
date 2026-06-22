<?php

namespace App\Http\Controllers\Technical;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TdAttempt;
use App\Models\TdSet;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $technicalClasses = $this->technicalClasses();
        $classIds = $technicalClasses->pluck('id')->values();
        $assignments = $this->technicalAssignments($classIds, false);
        $activeAssignments = $assignments->where('is_active', true);
        $teacherIds = $assignments->pluck('teacher_id')->filter()->unique()->values();

        $courses = $this->technicalCourses($classIds);
        $tdSets = $this->technicalTdSets($classIds);
        $attempts = $this->technicalAttempts($tdSets->pluck('id')->values());
        $studentProfiles = $this->technicalStudents($classIds);
        $courseProgress = $this->courseProgress($courses->pluck('id')->values());
        $progressReports = $this->progressReports($classIds);
        $reminders = $this->technicalReminders($classIds);
        $subjects = $this->subjects();
        $teachers = $this->teachers();

        $teacherCourseCounts = $courses->groupBy('created_by')->map->count();
        $teacherTdCounts = $tdSets->groupBy('author_user_id')->map->count();

        $teacherRows = $assignments->groupBy('teacher_id')->map(function ($items, $teacherId) use ($teacherCourseCounts, $teacherTdCounts) {
            $teacher = $items->first()->teacher ?? null;
            return [
                'id' => (int) $teacherId,
                'name' => $teacher->full_name ?? $teacher->name ?? $teacher->username ?? 'Enseignant non defini',
                'status' => $teacher->status ?? 'active',
                'subjects' => $items->pluck('subject.name')->filter()->unique()->values(),
                'classes' => $items->pluck('schoolClass.name')->filter()->unique()->values(),
                'active_assignments' => $items->where('is_active', true)->count(),
                'courses' => (int) ($teacherCourseCounts[$teacherId] ?? 0),
                'td' => (int) ($teacherTdCounts[$teacherId] ?? 0),
            ];
        })->values();

        $classRows = $technicalClasses->map(function ($class) use ($courses, $tdSets, $activeAssignments) {
            $classAssignments = $activeAssignments->where('school_class_id', $class->id);
            return [
                'id' => $class->id,
                'name' => $class->name,
                'description' => $class->description,
                'order' => $class->order,
                'is_active' => (bool) ($class->is_active ?? true),
                'level' => $class->level ?? 'enseignement_technique',
                'students' => (int) ($class->student_profiles_count ?? 0),
                'teachers' => $classAssignments->pluck('teacher_id')->filter()->unique()->count(),
                'subjects' => $classAssignments->pluck('subject.name')->filter()->unique()->values(),
                'published_courses' => $courses->where('school_class_id', $class->id)->where('status', Course::STATUS_PUBLISHED)->count(),
                'td' => $tdSets->where('school_class_id', $class->id)->count(),
            ];
        });

        $studentRows = $this->studentRows($studentProfiles, $courseProgress, $attempts, $progressReports, $reminders);
        $classTrackingRows = $this->classTrackingRows($technicalClasses, $studentProfiles, $courseProgress, $attempts, $progressReports);
        $teacherFollowupRows = $this->teacherFollowupRows($assignments, $courses, $tdSets, $reminders);
        $departmentRows = $this->departmentRows($subjects, $assignments, $courses, $tdSets, $reminders);
        $trackingStats = $this->trackingStats($studentRows, $courseProgress, $attempts, $progressReports, $teacherFollowupRows, $departmentRows, $reminders);

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

        $alerts = $this->buildAlerts($technicalClasses, $activeAssignments, $courses, $tdSets, $teacherRows);

        return view('technical.dashboard', [
            'stats' => $stats,
            'trackingStats' => $trackingStats,
            'technicalClasses' => $technicalClasses,
            'classRows' => $classRows,
            'classTrackingRows' => $classTrackingRows,
            'studentRows' => $studentRows,
            'teacherRows' => $teacherRows,
            'teacherFollowupRows' => $teacherFollowupRows,
            'departmentRows' => $departmentRows,
            'recentReminders' => $reminders->take(15)->values(),
            'assignments' => $assignments->sortBy(fn ($a) => ($a->schoolClass->name ?? '') . ($a->subject->name ?? ''))->values(),
            'subjects' => $subjects,
            'teachers' => $teachers,
            'recentCourses' => $courses->sortByDesc('updated_at')->take(12)->values(),
            'recentTds' => $tdSets->sortByDesc('updated_at')->take(12)->values(),
            'alerts' => $alerts,
            'courseStatusCounts' => $courses->groupBy('status')->map->count(),
            'tdStatusCounts' => $tdSets->groupBy('status')->map->count(),
        ]);
    }

    protected function technicalClasses(): Collection
    {
        if (!Schema::hasTable('school_classes')) return collect();
        $query = SchoolClass::query()->when(Schema::hasTable('student_profiles'), fn ($q) => $q->withCount('studentProfiles'))->orderBy('order')->orderBy('name');
        if (Schema::hasColumn('school_classes', 'level')) $query->where('level', 'enseignement_technique');
        return $query->get();
    }

    protected function technicalAssignments(Collection $classIds, bool $activeOnly = true): Collection
    {
        if ($classIds->isEmpty() || !Schema::hasTable('teacher_assignments')) return collect();
        return TeacherAssignment::query()->with(['teacher.roles', 'teacher.role', 'schoolClass', 'subject'])->whereIn('school_class_id', $classIds)->when($activeOnly && Schema::hasColumn('teacher_assignments', 'is_active'), fn ($q) => $q->where('is_active', true))->get();
    }

    protected function technicalCourses(Collection $classIds): Collection
    {
        if ($classIds->isEmpty() || !Schema::hasTable('courses')) return collect();
        return Course::query()->with(['schoolClass', 'subject', 'creator'])->whereIn('school_class_id', $classIds)->get();
    }

    protected function technicalTdSets(Collection $classIds): Collection
    {
        if ($classIds->isEmpty() || !Schema::hasTable('td_sets')) return collect();
        return TdSet::query()->with(['schoolClass', 'subject', 'author'])->whereIn('school_class_id', $classIds)->get();
    }

    protected function technicalAttempts(Collection $tdIds): Collection
    {
        if ($tdIds->isEmpty() || !Schema::hasTable('td_attempts')) return collect();
        return DB::table('td_attempts')->whereIn('td_set_id', $tdIds)->get();
    }

    protected function technicalStudents(Collection $classIds): Collection
    {
        if ($classIds->isEmpty() || !Schema::hasTable('student_profiles')) return collect();
        return StudentProfile::query()->with(['user', 'schoolClass'])->whereIn('school_class_id', $classIds)->get();
    }

    protected function courseProgress(Collection $courseIds): Collection
    {
        if ($courseIds->isEmpty() || !Schema::hasTable('course_progress')) return collect();
        return DB::table('course_progress')->whereIn('course_id', $courseIds)->get();
    }

    protected function progressReports(Collection $classIds): Collection
    {
        if ($classIds->isEmpty() || !Schema::hasTable('progress_reports')) return collect();
        return DB::table('progress_reports')->whereIn('school_class_id', $classIds)->orderByDesc('period_ends_at')->get();
    }

    protected function technicalReminders(Collection $classIds): Collection
    {
        if (!Schema::hasTable('technical_reminders')) return collect();
        return DB::table('technical_reminders')
            ->when($classIds->isNotEmpty(), fn ($q) => $q->where(function ($query) use ($classIds) { $query->whereNull('school_class_id')->orWhereIn('school_class_id', $classIds); }))
            ->orderByDesc('sent_at')
            ->orderByDesc('created_at')
            ->get();
    }

    protected function subjects(): Collection
    {
        if (!Schema::hasTable('subjects')) return collect();
        return Subject::query()->orderBy('order')->orderBy('name')->get();
    }

    protected function teachers(): Collection
    {
        if (!Schema::hasTable('users')) return collect();
        return User::query()->with(['roles', 'role'])->get()->filter(fn ($user) => method_exists($user, 'isTeacher') && $user->isTeacher())->sortBy(fn ($user) => $user->full_name ?? $user->name ?? $user->username)->values();
    }

    protected function countStudents(Collection $classIds): int
    {
        if ($classIds->isEmpty() || !Schema::hasTable('student_profiles')) return 0;
        return (int) StudentProfile::query()->whereIn('school_class_id', $classIds)->count();
    }

    protected function studentRows(Collection $students, Collection $progress, Collection $attempts, Collection $reports, Collection $reminders): Collection
    {
        $p = $progress->groupBy('student_id');
        $a = $attempts->groupBy('student_id');
        $r = $reports->groupBy('student_id');
        $rm = $reminders->where('target_type', 'student')->groupBy('target_user_id');
        return $students->map(function ($profile) use ($p, $a, $r, $rm) {
            $studentId = (int) ($profile->user_id ?? 0);
            $student = $profile->user;
            $sp = $p->get($studentId, collect());
            $sa = $a->get($studentId, collect());
            $sr = $r->get($studentId, collect());
            $late = $sa->whereIn('status', [TdAttempt::STATUS_EXPIRED, TdAttempt::STATUS_MISSED]);
            $submitted = $sa->whereIn('status', [TdAttempt::STATUS_SUBMITTED, TdAttempt::STATUS_COMPLETED, TdAttempt::STATUS_CORRECTED, TdAttempt::STATUS_GRADED]);
            $scores = $sa->pluck('score')->filter(fn ($score) => $score !== null && $score !== '');
            $status = $late->count() > 0 ? 'a relancer' : (($submitted->count() > 0 || $sp->where('status', 'completed')->count() > 0) ? 'actif' : (($sp->count() > 0 || $sa->count() > 0) ? 'en suivi' : 'a demarrer'));
            return [
                'id' => $studentId,
                'name' => $student->full_name ?? $student->name ?? $student->username ?? 'Eleve non defini',
                'class' => $profile->schoolClass->name ?? 'Classe non definie',
                'class_id' => $profile->school_class_id,
                'course_opened' => $sp->count(),
                'course_completed' => $sp->where('status', 'completed')->count(),
                'td_opened' => $sa->count(),
                'td_submitted' => $submitted->count(),
                'td_corrected' => $sa->whereIn('status', [TdAttempt::STATUS_CORRECTED, TdAttempt::STATUS_GRADED])->count(),
                'late_or_missed' => $late->count(),
                'reports' => $sr->count(),
                'reminders' => $rm->get($studentId, collect())->count(),
                'avg_score' => $scores->isNotEmpty() ? round((float) $scores->avg(), 2) : null,
                'status' => $status,
            ];
        })->values();
    }

    protected function classTrackingRows(Collection $classes, Collection $students, Collection $progress, Collection $attempts, Collection $reports): Collection
    {
        return $classes->map(function ($class) use ($students, $progress, $attempts, $reports) {
            $ids = $students->where('school_class_id', $class->id)->pluck('user_id')->filter()->values();
            $cp = $progress->whereIn('student_id', $ids);
            $ta = $attempts->whereIn('student_id', $ids);
            return [
                'class' => $class->name,
                'students' => $ids->count(),
                'course_opened' => $cp->count(),
                'course_completed' => $cp->where('status', 'completed')->count(),
                'td_submitted' => $ta->whereIn('status', [TdAttempt::STATUS_SUBMITTED, TdAttempt::STATUS_COMPLETED, TdAttempt::STATUS_CORRECTED, TdAttempt::STATUS_GRADED])->count(),
                'td_corrected' => $ta->whereIn('status', [TdAttempt::STATUS_CORRECTED, TdAttempt::STATUS_GRADED])->count(),
                'late_or_missed' => $ta->whereIn('status', [TdAttempt::STATUS_EXPIRED, TdAttempt::STATUS_MISSED])->count(),
                'reports' => $reports->whereIn('student_id', $ids)->count(),
            ];
        })->values();
    }

    protected function teacherFollowupRows(Collection $assignments, Collection $courses, Collection $tdSets, Collection $reminders): Collection
    {
        $remindersByTeacher = $reminders->where('target_type', 'teacher')->groupBy('target_user_id');
        return $assignments->groupBy('teacher_id')->map(function ($items, $teacherId) use ($courses, $tdSets, $remindersByTeacher) {
            $teacher = $items->first()->teacher ?? null;
            $teacherCourses = $courses->where('created_by', (int) $teacherId);
            $teacherTds = $tdSets->where('author_user_id', (int) $teacherId);
            $lastActivity = collect([$teacherCourses->max('updated_at'), $teacherTds->max('updated_at'), $items->max('updated_at')])->filter()->sortDesc()->first();
            $status = 'normal';
            if (($teacher->status ?? 'active') !== 'active') $status = 'inactif';
            elseif ($teacherCourses->count() === 0 && $teacherTds->count() === 0) $status = 'a relancer';
            elseif ($teacherCourses->where('status', Course::STATUS_DRAFT)->count() > 0) $status = 'a suivre';
            return [
                'id' => (int) $teacherId,
                'name' => $teacher->full_name ?? $teacher->name ?? $teacher->username ?? 'Enseignant non defini',
                'status' => $status,
                'account_status' => $teacher->status ?? 'active',
                'classes' => $items->pluck('schoolClass.name')->filter()->unique()->values(),
                'subjects' => $items->pluck('subject.name')->filter()->unique()->values(),
                'assignments' => $items->count(),
                'courses' => $teacherCourses->count(),
                'published_courses' => $teacherCourses->where('status', Course::STATUS_PUBLISHED)->count(),
                'draft_courses' => $teacherCourses->where('status', Course::STATUS_DRAFT)->count(),
                'td' => $teacherTds->count(),
                'published_td' => $teacherTds->where('status', TdSet::STATUS_PUBLISHED)->count(),
                'reminders' => $remindersByTeacher->get((int) $teacherId, collect())->count(),
                'last_activity' => $lastActivity,
            ];
        })->values();
    }

    protected function departmentRows(Collection $subjects, Collection $assignments, Collection $courses, Collection $tdSets, Collection $reminders): Collection
    {
        $departments = collect();
        if (Schema::hasTable('technical_departments')) {
            $departments = DB::table('technical_departments')->leftJoin('users', 'users.id', '=', 'technical_departments.responsible_user_id')->leftJoin('subjects', 'subjects.id', '=', 'technical_departments.subject_id')->select('technical_departments.*', 'users.full_name as responsible_full_name', 'users.name as responsible_name', 'users.username as responsible_username', 'subjects.name as subject_name')->orderBy('technical_departments.name')->get();
        }

        if ($departments->isEmpty()) {
            $departments = $subjects->map(function ($subject) {
                return (object) ['id' => 0, 'name' => $subject->name, 'subject_id' => $subject->id, 'school_class_id' => null, 'responsible_user_id' => null, 'responsible_full_name' => null, 'responsible_name' => null, 'responsible_username' => null, 'subject_name' => $subject->name, 'status' => 'a_configurer'];
            });
        }

        $remindersByUser = $reminders->where('target_type', 'department_responsible')->groupBy('target_user_id');
        return $departments->map(function ($department) use ($assignments, $courses, $tdSets, $remindersByUser) {
            $subjectId = (int) ($department->subject_id ?? 0);
            $departmentAssignments = $subjectId ? $assignments->where('subject_id', $subjectId) : collect();
            $departmentCourses = $subjectId ? $courses->where('subject_id', $subjectId) : collect();
            $departmentTds = $subjectId ? $tdSets->where('subject_id', $subjectId) : collect();
            $responsibleId = (int) ($department->responsible_user_id ?? 0);
            $status = ($department->status ?? 'active') === 'active' ? 'normal' : 'inactif';
            if (!$responsibleId) $status = 'sans responsable';
            elseif ($departmentCourses->where('status', Course::STATUS_PUBLISHED)->count() === 0) $status = 'a relancer';
            return [
                'id' => (int) ($department->id ?? 0),
                'name' => $department->name ?? 'Departement',
                'subject_id' => $subjectId ?: null,
                'class_id' => (int) ($department->school_class_id ?? 0) ?: null,
                'responsible_id' => $responsibleId ?: null,
                'responsible_name' => $department->responsible_full_name ?? $department->responsible_name ?? $department->responsible_username ?? 'Non defini',
                'status' => $status,
                'teachers' => $departmentAssignments->pluck('teacher_id')->filter()->unique()->count(),
                'courses' => $departmentCourses->count(),
                'published_courses' => $departmentCourses->where('status', Course::STATUS_PUBLISHED)->count(),
                'td' => $departmentTds->count(),
                'published_td' => $departmentTds->where('status', TdSet::STATUS_PUBLISHED)->count(),
                'reminders' => $responsibleId ? $remindersByUser->get($responsibleId, collect())->count() : 0,
            ];
        })->values();
    }

    protected function trackingStats(Collection $students, Collection $progress, Collection $attempts, Collection $reports, Collection $teachers, Collection $departments, Collection $reminders): array
    {
        $scores = $attempts->pluck('score')->filter(fn ($score) => $score !== null && $score !== '');
        return [
            'students' => $students->count(),
            'active_students' => $students->whereIn('status', ['actif', 'en suivi'])->count(),
            'students_to_restart' => $students->where('status', 'a relancer')->count(),
            'teachers_to_restart' => $teachers->where('status', 'a relancer')->count(),
            'departments_to_restart' => $departments->whereIn('status', ['a relancer', 'sans responsable'])->count(),
            'course_opened' => $progress->count(),
            'course_completed' => $progress->where('status', 'completed')->count(),
            'td_opened' => $attempts->count(),
            'td_submitted' => $attempts->whereIn('status', [TdAttempt::STATUS_SUBMITTED, TdAttempt::STATUS_COMPLETED, TdAttempt::STATUS_CORRECTED, TdAttempt::STATUS_GRADED])->count(),
            'td_corrected' => $attempts->whereIn('status', [TdAttempt::STATUS_CORRECTED, TdAttempt::STATUS_GRADED])->count(),
            'late_or_missed' => $attempts->whereIn('status', [TdAttempt::STATUS_EXPIRED, TdAttempt::STATUS_MISSED])->count(),
            'reports' => $reports->count(),
            'reminders' => $reminders->count(),
            'avg_score' => $scores->isNotEmpty() ? round((float) $scores->avg(), 2) : null,
        ];
    }

    protected function buildAlerts(Collection $classes, Collection $assignments, Collection $courses, Collection $tdSets, Collection $teacherRows): Collection
    {
        $alerts = collect();
        if ($classes->isEmpty()) {
            $alerts->push(['level' => 'warning', 'title' => 'Aucune classe technique configuree', 'message' => 'Ajoutez directement les classes techniques depuis ce tableau de bord.']);
            return $alerts;
        }
        foreach ($classes as $class) {
            $classAssignments = $assignments->where('school_class_id', $class->id);
            $publishedCourses = $courses->where('school_class_id', $class->id)->where('status', Course::STATUS_PUBLISHED)->count();
            $publishedTd = $tdSets->where('school_class_id', $class->id)->where('status', TdSet::STATUS_PUBLISHED)->count();
            if ($classAssignments->isEmpty()) $alerts->push(['level' => 'danger', 'title' => $class->name . ' sans enseignant actif', 'message' => 'Cette classe technique doit recevoir au moins une affectation enseignant active.']);
            if ($publishedCourses === 0) $alerts->push(['level' => 'warning', 'title' => $class->name . ' sans cours publie', 'message' => 'Aucun cours publie n est visible pour cette classe technique.']);
            if ($publishedTd === 0) $alerts->push(['level' => 'info', 'title' => $class->name . ' sans TD publie', 'message' => 'Aucun TD publie n est actuellement disponible pour cette classe.']);
        }
        foreach ($teacherRows as $teacher) {
            if (($teacher['status'] ?? 'active') !== 'active') $alerts->push(['level' => 'danger', 'title' => $teacher['name'] . ' inactif', 'message' => 'Ce compte enseignant est rattache a la section technique mais son statut n est pas actif.']);
            if (($teacher['courses'] ?? 0) === 0) $alerts->push(['level' => 'warning', 'title' => $teacher['name'] . ' sans cours', 'message' => 'Aucun cours technique n est associe a cet enseignant.']);
        }
        $draftCourses = $courses->where('status', Course::STATUS_DRAFT)->count();
        if ($draftCourses > 0) $alerts->push(['level' => 'info', 'title' => $draftCourses . ' cours en brouillon', 'message' => 'Des contenus techniques doivent encore etre publies ou finalises.']);
        return $alerts->take(12)->values();
    }
}
