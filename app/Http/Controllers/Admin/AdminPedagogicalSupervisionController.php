<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminPedagogicalSupervisionController extends Controller
{
    public function index()
    {
        $ready = collect(['teaching_divisions', 'teaching_departments', 'pedagogical_responsibilities', 'pedagogical_supervision_notes'])->every(fn ($table) => Schema::hasTable($table));

        $data = [
            'schemaReady' => $ready,
            'stats' => [],
            'divisions' => collect(),
            'departments' => collect(),
            'responsibilities' => collect(),
            'notes' => collect(),
            'users' => collect(),
            'subjects' => collect(),
            'classes' => collect(),
            'departmentReports' => collect(),
        ];

        if (!$ready) {
            return view('admin.pedagogical-supervision.index', $data);
        }

        $data['stats'] = [
            'divisions' => DB::table('teaching_divisions')->count(),
            'departments' => DB::table('teaching_departments')->count(),
            'active_responsibilities' => DB::table('pedagogical_responsibilities')->where('is_active', true)->count(),
            'open_notes' => DB::table('pedagogical_supervision_notes')->whereIn('status', ['open', 'pending'])->count(),
            'courses_published' => Schema::hasTable('courses') ? DB::table('courses')->where('status', 'published')->count() : 0,
            'courses_draft' => Schema::hasTable('courses') ? DB::table('courses')->where('status', 'draft')->count() : 0,
            'td_published' => Schema::hasTable('td_sets') ? DB::table('td_sets')->where('status', 'published')->count() : 0,
            'td_draft' => Schema::hasTable('td_sets') ? DB::table('td_sets')->where('status', 'draft')->count() : 0,
            'td_questions_open' => Schema::hasTable('td_question_threads') ? DB::table('td_question_threads')->where('status', 'open')->count() : 0,
            'teacher_messages_unread' => Schema::hasTable('teacher_messages') ? DB::table('teacher_messages')->where('status', 'unread')->count() : 0,
            'td_attempts_completed' => Schema::hasTable('td_attempts') ? DB::table('td_attempts')->whereIn('status', ['completed', 'submitted', 'corrected', 'graded'])->count() : 0,
            'weekly_programs' => Schema::hasTable('teacher_weekly_programs') ? DB::table('teacher_weekly_programs')->count() : 0,
        ];

        $data['divisions'] = DB::table('teaching_divisions')->orderBy('order')->orderBy('name')->get();
        $data['departments'] = DB::table('teaching_departments')->leftJoin('teaching_divisions', 'teaching_divisions.id', '=', 'teaching_departments.teaching_division_id')->select('teaching_departments.*', 'teaching_divisions.name as division_name')->orderBy('teaching_departments.order')->orderBy('teaching_departments.name')->get();
        $data['responsibilities'] = DB::table('pedagogical_responsibilities')->leftJoin('users', 'users.id', '=', 'pedagogical_responsibilities.user_id')->leftJoin('teaching_divisions', 'teaching_divisions.id', '=', 'pedagogical_responsibilities.teaching_division_id')->leftJoin('teaching_departments', 'teaching_departments.id', '=', 'pedagogical_responsibilities.teaching_department_id')->select('pedagogical_responsibilities.*', 'users.full_name', 'users.name', 'users.username', 'teaching_divisions.name as division_name', 'teaching_departments.name as department_name')->orderByDesc('pedagogical_responsibilities.id')->limit(100)->get();
        $data['notes'] = DB::table('pedagogical_supervision_notes')->leftJoin('users as targets', 'targets.id', '=', 'pedagogical_supervision_notes.target_user_id')->leftJoin('teaching_divisions', 'teaching_divisions.id', '=', 'pedagogical_supervision_notes.teaching_division_id')->leftJoin('teaching_departments', 'teaching_departments.id', '=', 'pedagogical_supervision_notes.teaching_department_id')->select('pedagogical_supervision_notes.*', 'targets.full_name as target_name', 'teaching_divisions.name as division_name', 'teaching_departments.name as department_name')->orderByDesc('pedagogical_supervision_notes.id')->limit(50)->get();
        $data['users'] = Schema::hasTable('users') ? DB::table('users')->select('id', 'name', 'full_name', 'username', 'phone', 'email')->orderBy('full_name')->orderBy('name')->limit(500)->get() : collect();
        $data['subjects'] = Schema::hasTable('subjects') ? DB::table('subjects')->select('id', 'name')->orderBy('name')->get() : collect();
        $data['classes'] = Schema::hasTable('school_classes') ? DB::table('school_classes')->select('id', 'name')->orderBy('name')->get() : collect();
        $data['departmentReports'] = $this->departmentReports($data['departments']);

        return view('admin.pedagogical-supervision.index', $data);
    }

    public function storeDivision(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:180'], 'type' => ['nullable', 'string', 'max:60'], 'description' => ['nullable', 'string', 'max:2000'], 'order' => ['nullable', 'integer']]);
        DB::table('teaching_divisions')->insert(['name' => $data['name'], 'slug' => $this->slug('teaching_divisions', $data['name']), 'type' => $data['type'] ?: 'general', 'description' => $data['description'] ?? null, 'order' => (int) ($data['order'] ?? 0), 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Type d’enseignement ajouté.');
    }

    public function storeDepartment(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:180'], 'teaching_division_id' => ['nullable', 'integer'], 'subject_id' => ['nullable', 'integer'], 'school_class_id' => ['nullable', 'integer'], 'code' => ['nullable', 'string', 'max:30'], 'description' => ['nullable', 'string', 'max:2000'], 'order' => ['nullable', 'integer']]);
        DB::table('teaching_departments')->insert(['teaching_division_id' => $data['teaching_division_id'] ?: null, 'subject_id' => $data['subject_id'] ?: null, 'school_class_id' => $data['school_class_id'] ?: null, 'name' => $data['name'], 'slug' => $this->slug('teaching_departments', $data['name']), 'code' => $data['code'] ?? null, 'description' => $data['description'] ?? null, 'order' => (int) ($data['order'] ?? 0), 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Département ou filière ajouté.');
    }

    public function storeResponsibility(Request $request)
    {
        $data = $request->validate(['user_id' => ['required', 'integer'], 'role_title' => ['required', 'string', 'max:120'], 'scope_type' => ['required', 'string'], 'teaching_division_id' => ['nullable', 'integer'], 'teaching_department_id' => ['nullable', 'integer'], 'can_validate_content' => ['nullable'], 'notes' => ['nullable', 'string', 'max:3000']]);
        $scope = in_array($data['scope_type'], ['platform', 'division', 'department'], true) ? $data['scope_type'] : 'platform';
        DB::table('pedagogical_responsibilities')->insert(['user_id' => $data['user_id'], 'role_title' => $data['role_title'], 'scope_type' => $scope, 'teaching_division_id' => $scope === 'division' ? ($data['teaching_division_id'] ?: null) : null, 'teaching_department_id' => $scope === 'department' ? ($data['teaching_department_id'] ?: null) : null, 'can_view_reports' => 1, 'can_send_alerts' => 1, 'can_validate_content' => $request->boolean('can_validate_content'), 'is_active' => 1, 'notes' => $data['notes'] ?? null, 'assigned_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Responsabilité attribuée.');
    }

    public function storeNote(Request $request)
    {
        $data = $request->validate(['responsibility_id' => ['nullable', 'integer'], 'target_user_id' => ['nullable', 'integer'], 'teaching_division_id' => ['nullable', 'integer'], 'teaching_department_id' => ['nullable', 'integer'], 'title' => ['required', 'string', 'max:180'], 'message' => ['nullable', 'string', 'max:3000'], 'severity' => ['nullable', 'string']]);
        $severity = in_array($data['severity'] ?? 'info', ['info', 'warning', 'urgent'], true) ? ($data['severity'] ?? 'info') : 'info';
        DB::table('pedagogical_supervision_notes')->insert(['responsibility_id' => $data['responsibility_id'] ?: null, 'author_id' => $request->user()?->id, 'target_user_id' => $data['target_user_id'] ?: null, 'teaching_division_id' => $data['teaching_division_id'] ?: null, 'teaching_department_id' => $data['teaching_department_id'] ?: null, 'title' => $data['title'], 'message' => $data['message'] ?? null, 'severity' => $severity, 'status' => 'open', 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Note de suivi ajoutée.');
    }

    public function toggleResponsibility($responsibility)
    {
        $row = DB::table('pedagogical_responsibilities')->where('id', $responsibility)->first();
        if ($row) DB::table('pedagogical_responsibilities')->where('id', $responsibility)->update(['is_active' => !$row->is_active, 'updated_at' => now()]);
        return back()->with('success', 'Statut mis à jour.');
    }

    public function updateNoteStatus(Request $request, $note)
    {
        $status = in_array($request->input('status'), ['open', 'pending', 'resolved', 'closed'], true) ? $request->input('status') : 'open';
        DB::table('pedagogical_supervision_notes')->where('id', $note)->update(['status' => $status, 'resolved_at' => in_array($status, ['resolved', 'closed'], true) ? now() : null, 'updated_at' => now()]);
        return back()->with('success', 'Note mise à jour.');
    }

    private function departmentReports($departments)
    {
        return $departments->map(function ($department) {
            $courses = Schema::hasTable('courses') ? DB::table('courses') : null;
            $td = Schema::hasTable('td_sets') ? DB::table('td_sets') : null;
            $questions = Schema::hasTable('td_question_threads') ? DB::table('td_question_threads') : null;
            foreach ([$courses, $td, $questions] as $query) {
                if ($query && $department->subject_id) $query->where('subject_id', $department->subject_id);
                if ($query && $department->school_class_id) $query->where('school_class_id', $department->school_class_id);
            }
            return ['department' => $department, 'courses' => $courses?->count() ?? 0, 'td' => $td?->count() ?? 0, 'open_questions' => $questions?->where('status', 'open')->count() ?? 0];
        });
    }

    private function slug(string $table, string $name): string
    {
        $base = Str::slug($name) ?: 'element';
        $slug = $base;
        $i = 2;
        while (DB::table($table)->where('slug', $slug)->exists()) $slug = $base . '-' . $i++;
        return $slug;
    }
}
