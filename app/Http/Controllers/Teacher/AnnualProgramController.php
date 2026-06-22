<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\TdSet;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnnualProgramController extends Controller
{
    public function index()
    {
        if (!Schema::hasTable('annual_programs')) {
            return view('teacher.annual_programs.index', [
                'programs' => collect(),
                'assignments' => $this->assignments(),
                'courses' => collect(),
                'tdSets' => collect(),
                'migrationMissing' => true,
            ]);
        }

        $assignments = $this->assignments();
        $programs = DB::table('annual_programs')
            ->leftJoin('school_classes', 'school_classes.id', '=', 'annual_programs.school_class_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'annual_programs.subject_id')
            ->select('annual_programs.*', 'school_classes.name as class_name', 'subjects.name as subject_name')
            ->where('annual_programs.teacher_id', auth()->id())
            ->orderByDesc('annual_programs.updated_at')
            ->get();

        $programItems = Schema::hasTable('annual_program_items')
            ? DB::table('annual_program_items')->whereIn('annual_program_id', $programs->pluck('id'))->orderBy('order')->get()->groupBy('annual_program_id')
            : collect();

        $programs = $programs->map(function ($program) use ($programItems) {
            $program->items = $programItems->get($program->id, collect());
            return $program;
        });

        return view('teacher.annual_programs.index', [
            'programs' => $programs,
            'assignments' => $assignments,
            'courses' => $this->teacherCourses($assignments),
            'tdSets' => $this->teacherTdSets($assignments),
            'migrationMissing' => false,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'teacher_assignment_id' => ['required', 'integer'],
            'school_year' => ['required', 'string', 'max:20'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,published,archived'],
        ]);

        if (!Schema::hasTable('annual_programs')) {
            return back()->with('error', 'La migration du programme annuel n est pas encore lancee.');
        }

        $assignment = $this->assignments()->firstWhere('id', (int) $request->teacher_assignment_id);
        abort_unless($assignment, 403);

        DB::table('annual_programs')->insert([
            'teacher_id' => auth()->id(),
            'school_class_id' => $assignment->school_class_id,
            'subject_id' => $assignment->subject_id,
            'school_year' => $request->school_year,
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?: 'draft',
            'published_at' => $request->status === 'published' ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Programme annuel créé. Ajoutez maintenant les chapitres.');
    }

    public function storeItem(Request $request, int $program)
    {
        $programRow = $this->ownedProgram($program);

        $request->validate([
            'period_label' => ['nullable', 'string', 'max:80'],
            'chapter_title' => ['required', 'string', 'max:255'],
            'objectives' => ['nullable', 'string'],
            'course_id' => ['nullable', 'integer'],
            'td_set_id' => ['nullable', 'integer'],
            'order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:planned,in_progress,completed,late'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date'],
        ]);

        if ($request->filled('course_id')) {
            $this->authorizeCourse((int) $request->course_id, $programRow);
        }
        if ($request->filled('td_set_id')) {
            $this->authorizeTd((int) $request->td_set_id, $programRow);
        }

        DB::table('annual_program_items')->insert([
            'annual_program_id' => $program,
            'course_id' => $request->integer('course_id') ?: null,
            'td_set_id' => $request->integer('td_set_id') ?: null,
            'period_label' => $request->period_label,
            'chapter_title' => $request->chapter_title,
            'objectives' => $request->objectives,
            'order' => (int) ($request->order ?? 0),
            'status' => $request->status ?: 'planned',
            'starts_on' => $request->starts_on ?: null,
            'ends_on' => $request->ends_on ?: null,
            'completed_at' => $request->status === 'completed' ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Chapitre ajouté au programme annuel.');
    }

    public function publish(int $program)
    {
        $this->ownedProgram($program);
        DB::table('annual_programs')->where('id', $program)->update(['status' => 'published', 'published_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Programme annuel publié. Il devient visible aux élèves, parents et responsables.');
    }

    public function archive(int $program)
    {
        $this->ownedProgram($program);
        DB::table('annual_programs')->where('id', $program)->update(['status' => 'archived', 'updated_at' => now()]);
        return back()->with('success', 'Programme annuel archivé.');
    }

    public function delete(int $program)
    {
        $this->ownedProgram($program);
        DB::table('annual_programs')->where('id', $program)->delete();
        return back()->with('success', 'Programme annuel supprimé.');
    }

    public function completeItem(int $item)
    {
        $row = DB::table('annual_program_items')->where('id', $item)->first();
        abort_unless($row, 404);
        $this->ownedProgram((int) $row->annual_program_id);
        DB::table('annual_program_items')->where('id', $item)->update(['status' => 'completed', 'completed_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Chapitre marqué comme terminé.');
    }

    public function deleteItem(int $item)
    {
        $row = DB::table('annual_program_items')->where('id', $item)->first();
        abort_unless($row, 404);
        $this->ownedProgram((int) $row->annual_program_id);
        DB::table('annual_program_items')->where('id', $item)->delete();
        return back()->with('success', 'Chapitre retiré du programme.');
    }

    protected function ownedProgram(int $program)
    {
        abort_unless(Schema::hasTable('annual_programs'), 404);
        $row = DB::table('annual_programs')->where('id', $program)->where('teacher_id', auth()->id())->first();
        abort_unless($row, 404);
        return $row;
    }

    protected function authorizeCourse(int $courseId, $program): void
    {
        $ok = Course::query()
            ->where('id', $courseId)
            ->where('created_by', auth()->id())
            ->where('school_class_id', $program->school_class_id)
            ->where('subject_id', $program->subject_id)
            ->exists();
        abort_unless($ok, 403);
    }

    protected function authorizeTd(int $tdId, $program): void
    {
        $ok = TdSet::query()
            ->where('id', $tdId)
            ->where('author_user_id', auth()->id())
            ->where('school_class_id', $program->school_class_id)
            ->where('subject_id', $program->subject_id)
            ->exists();
        abort_unless($ok, 403);
    }

    protected function assignments(): Collection
    {
        if (!Schema::hasTable('teacher_assignments')) {
            return collect();
        }

        return TeacherAssignment::query()
            ->with(['schoolClass', 'subject'])
            ->where('teacher_id', auth()->id())
            ->where('is_active', true)
            ->orderByDesc('id')
            ->get();
    }

    protected function teacherCourses(Collection $assignments): Collection
    {
        if ($assignments->isEmpty()) return collect();
        return Course::query()
            ->where('created_by', auth()->id())
            ->where(function ($query) use ($assignments) {
                foreach ($assignments as $assignment) {
                    $query->orWhere(function ($inner) use ($assignment) {
                        $inner->where('school_class_id', $assignment->school_class_id)->where('subject_id', $assignment->subject_id);
                    });
                }
            })
            ->orderBy('title')
            ->get();
    }

    protected function teacherTdSets(Collection $assignments): Collection
    {
        if ($assignments->isEmpty()) return collect();
        return TdSet::query()
            ->where('author_user_id', auth()->id())
            ->where(function ($query) use ($assignments) {
                foreach ($assignments as $assignment) {
                    $query->orWhere(function ($inner) use ($assignment) {
                        $inner->where('school_class_id', $assignment->school_class_id)->where('subject_id', $assignment->subject_id);
                    });
                }
            })
            ->orderBy('title')
            ->get();
    }
}
