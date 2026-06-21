<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SupervisionDashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!$this->schemaReady()) {
            return view('supervision.dashboard', $this->emptyData(false));
        }

        $responsibilities = $this->responsibilities((int) $request->user()->id);
        if ($responsibilities->isEmpty()) {
            abort(403, 'Aucune responsabilité pédagogique active ne vous est attribuée.');
        }

        $activeResponsibility = $responsibilities->firstWhere('id', (int) $request->query('responsibility')) ?: $responsibilities->first();

        return view('supervision.dashboard', [
            'schemaReady' => true,
            'responsibilities' => $responsibilities,
            'activeResponsibility' => $activeResponsibility,
            'areaTitle' => $this->areaTitle($activeResponsibility),
            'stats' => $this->stats(),
            'teachers' => $this->teachers(),
            'courses' => $this->latestItems('courses'),
            'tdSets' => $this->latestItems('td_sets'),
            'questions' => $this->latestQuestions(),
            'notes' => $this->notes((int) $activeResponsibility->id),
        ]);
    }

    public function storeNote(Request $request)
    {
        if (!$this->schemaReady()) {
            return back()->with('error', 'Les tables de supervision ne sont pas encore installées.');
        }

        $responsibilities = $this->responsibilities((int) $request->user()->id);
        $responsibility = $responsibilities->firstWhere('id', (int) $request->input('responsibility_id'));

        if (!$responsibility) {
            abort(403);
        }

        $data = $request->validate([
            'responsibility_id' => ['required', 'integer'],
            'target_user_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'severity' => ['nullable', 'string', 'in:info,warning,urgent'],
        ]);

        DB::table('pedagogical_supervision_notes')->insert([
            'responsibility_id' => $responsibility->id,
            'author_id' => $request->user()->id,
            'target_user_id' => $data['target_user_id'] ?? null,
            'teaching_division_id' => $responsibility->teaching_division_id,
            'teaching_department_id' => $responsibility->teaching_department_id,
            'title' => $data['title'],
            'message' => $data['message'] ?? null,
            'severity' => $data['severity'] ?? 'info',
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Note de suivi enregistrée.');
    }

    private function emptyData(bool $schemaReady): array
    {
        return [
            'schemaReady' => $schemaReady,
            'responsibilities' => collect(),
            'activeResponsibility' => null,
            'areaTitle' => 'Supervision pédagogique',
            'stats' => [],
            'teachers' => collect(),
            'courses' => collect(),
            'tdSets' => collect(),
            'questions' => collect(),
            'notes' => collect(),
        ];
    }

    private function schemaReady(): bool
    {
        return Schema::hasTable('pedagogical_responsibilities')
            && Schema::hasTable('teaching_divisions')
            && Schema::hasTable('teaching_departments')
            && Schema::hasTable('pedagogical_supervision_notes');
    }

    private function responsibilities(int $userId)
    {
        return DB::table('pedagogical_responsibilities as pr')
            ->leftJoin('teaching_divisions as division', 'division.id', '=', 'pr.teaching_division_id')
            ->leftJoin('teaching_departments as department', 'department.id', '=', 'pr.teaching_department_id')
            ->where('pr.user_id', $userId)
            ->where('pr.is_active', true)
            ->select('pr.*', 'division.name as division_name', 'department.name as department_name')
            ->orderBy('pr.scope_type')
            ->get();
    }

    private function stats(): array
    {
        return [
            'teachers' => Schema::hasTable('teacher_assignments') ? DB::table('teacher_assignments')->where('is_active', true)->distinct()->count('teacher_id') : 0,
            'courses_published' => Schema::hasTable('courses') ? DB::table('courses')->where('status', 'published')->count() : 0,
            'courses_draft' => Schema::hasTable('courses') ? DB::table('courses')->where('status', 'draft')->count() : 0,
            'td_published' => Schema::hasTable('td_sets') ? DB::table('td_sets')->where('status', 'published')->count() : 0,
            'questions_open' => Schema::hasTable('td_question_threads') ? DB::table('td_question_threads')->where('status', 'open')->count() : 0,
        ];
    }

    private function teachers()
    {
        if (!Schema::hasTable('teacher_assignments') || !Schema::hasTable('users')) {
            return collect();
        }

        return DB::table('teacher_assignments as ta')
            ->join('users as u', 'u.id', '=', 'ta.teacher_id')
            ->leftJoin('school_classes as c', 'c.id', '=', 'ta.school_class_id')
            ->leftJoin('subjects as s', 's.id', '=', 'ta.subject_id')
            ->where('ta.is_active', true)
            ->select('u.id', 'u.full_name', 'u.name', 'u.username', 'c.name as class_name', 's.name as subject_name')
            ->orderByDesc('ta.id')
            ->limit(12)
            ->get();
    }

    private function latestItems(string $table)
    {
        if (!Schema::hasTable($table)) {
            return collect();
        }

        return DB::table($table . ' as item')
            ->leftJoin('school_classes as c', 'c.id', '=', 'item.school_class_id')
            ->leftJoin('subjects as s', 's.id', '=', 'item.subject_id')
            ->select('item.id', 'item.title', 'item.status', 'c.name as class_name', 's.name as subject_name')
            ->orderByDesc('item.id')
            ->limit(10)
            ->get();
    }

    private function latestQuestions()
    {
        if (!Schema::hasTable('td_question_threads')) {
            return collect();
        }

        return DB::table('td_question_threads as q')
            ->leftJoin('school_classes as c', 'c.id', '=', 'q.school_class_id')
            ->leftJoin('subjects as s', 's.id', '=', 'q.subject_id')
            ->select('q.id', 'q.subject', 'q.status', 'c.name as class_name', 's.name as subject_name')
            ->orderByDesc('q.id')
            ->limit(10)
            ->get();
    }

    private function notes(int $responsibilityId)
    {
        return DB::table('pedagogical_supervision_notes as n')
            ->leftJoin('users as u', 'u.id', '=', 'n.target_user_id')
            ->where('n.responsibility_id', $responsibilityId)
            ->select('n.*', 'u.full_name', 'u.name', 'u.username')
            ->orderByDesc('n.id')
            ->limit(12)
            ->get();
    }

    private function areaTitle(object $responsibility): string
    {
        if ($responsibility->scope_type === 'division') {
            return $responsibility->division_name ?: 'Type d’enseignement';
        }

        if ($responsibility->scope_type === 'department') {
            return $responsibility->department_name ?: 'Département / filière';
        }

        return 'Plateforme entière';
    }
}
