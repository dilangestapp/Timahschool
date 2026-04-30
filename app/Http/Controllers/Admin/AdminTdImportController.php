<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminTdImportController extends Controller
{
    public function create(Request $request)
    {
        $parsed = null;

        if ($request->old('import_block')) {
            $parsed = $this->parseImportBlock((string) $request->old('import_block'));
        }

        return view('admin.td.import', [
            'parsed' => $parsed,
        ]);
    }

    public function analyze(Request $request)
    {
        $data = $request->validate([
            'import_block' => ['required', 'string', 'min:20'],
        ], [
            'import_block.required' => 'Collez le bloc du TD avant de lancer l’analyse.',
        ]);

        return back()
            ->withInput()
            ->with('parsed_td_import', $this->parseImportBlock($data['import_block']))
            ->with('info', 'Analyse terminée. Vérifiez les éléments détectés avant l’import.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'import_block' => ['required', 'string', 'min:20'],
            'force_status' => ['nullable', 'in:draft,published'],
            'access_level' => ['nullable', 'in:free,premium'],
            'difficulty' => ['nullable', 'in:easy,medium,hard,exam'],
        ]);

        $parsed = $this->parseImportBlock($data['import_block']);

        if (!$parsed['title'] || !$parsed['class'] || !$parsed['subject'] || !$parsed['content']) {
            return back()
                ->withInput()
                ->with('parsed_td_import', $parsed)
                ->with('error', 'Import impossible : titre, classe, matière ou contenu TD manquant.');
        }

        DB::beginTransaction();

        try {
            $now = now();
            $classId = $this->ensureClass($parsed['class']);
            $subjectId = $this->ensureSubject($parsed['subject']);
            $assignment = $this->findAssignment($classId, $subjectId);

            $status = $data['force_status'] ?? $parsed['status'] ?? 'draft';
            $status = $status === 'published' ? 'published' : 'draft';

            $title = trim($parsed['title']);
            $slugBase = Str::slug($title) ?: 'td-importe';
            $slug = $slugBase.'-'.now()->format('YmdHis');

            DB::table('td_sets')->insert([
                'school_class_id' => $classId,
                'subject_id' => $subjectId,
                'teacher_assignment_id' => $assignment?->id,
                'author_user_id' => $assignment?->teacher_id ?: auth()->id(),
                'title' => $title,
                'slug' => $slug,
                'chapter_label' => $parsed['chapter'] ?: null,
                'difficulty' => $data['difficulty'] ?? 'medium',
                'access_level' => $data['access_level'] ?? 'free',
                'correction_delay_minutes' => 30,
                'status' => $status,
                'editable_html' => $this->toHtml($parsed['content']),
                'editable_text' => $parsed['content'],
                'has_editable_version' => true,
                'correction_html' => $parsed['correction'] ? $this->toHtml($parsed['correction']) : null,
                'published_at' => $status === 'published' ? $now : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.td.index')
                ->with('success', 'TD importé avec succès dans TIMAH ACADEMY.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('parsed_td_import', $parsed)
                ->with('error', 'Erreur pendant l’import : '.$e->getMessage());
        }
    }

    protected function parseImportBlock(string $raw): array
    {
        $text = trim(str_replace(["\r\n", "\r"], "\n", $raw));

        return [
            'title' => $this->firstValue($text, ['TITRE', 'TITLE', 'SUJET']),
            'class' => $this->firstValue($text, ['CLASSE', 'CLASS', 'NIVEAU']),
            'subject' => $this->firstValue($text, ['MATIÈRE', 'MATIERE', 'SUBJECT']),
            'status' => $this->normalizeStatus($this->firstValue($text, ['STATUT', 'STATUS'])),
            'chapter' => $this->firstValue($text, ['CHAPITRE', 'THÈME', 'THEME']),
            'content' => $this->section($text, ['CONTENU_TD', 'CONTENU TD', 'TD', 'SUJET_TD'], ['CORRIGE', 'CORRIGÉ', 'CORRECTION', 'FIN_IMPORT_TIMAH_ACADEMY']),
            'correction' => $this->section($text, ['CORRIGE', 'CORRIGÉ', 'CORRECTION'], ['FIN_IMPORT_TIMAH_ACADEMY']),
            'raw' => $text,
        ];
    }

    protected function firstValue(string $text, array $labels): ?string
    {
        foreach ($labels as $label) {
            $pattern = '/^\s*'.preg_quote($label, '/').'\s*[:：-]\s*(.+)$/imu';
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    protected function section(string $text, array $starts, array $ends): string
    {
        foreach ($starts as $start) {
            $startPattern = '/^\s*'.preg_quote($start, '/').'\s*[:：-]?\s*$/imu';
            if (preg_match($startPattern, $text, $match, PREG_OFFSET_CAPTURE)) {
                $from = $match[0][1] + strlen($match[0][0]);
                $to = strlen($text);

                foreach ($ends as $end) {
                    $endPattern = '/^\s*'.preg_quote($end, '/').'\s*[:：-]?\s*$/imu';
                    if (preg_match($endPattern, $text, $endMatch, PREG_OFFSET_CAPTURE, $from)) {
                        $to = min($to, $endMatch[0][1]);
                    }
                }

                return trim(substr($text, $from, $to - $from));
            }
        }

        return '';
    }

    protected function normalizeStatus(?string $status): string
    {
        $status = Str::lower(trim((string) $status));
        return in_array($status, ['publie', 'publié', 'published', 'public'], true) ? 'published' : 'draft';
    }

    protected function ensureClass(string $name): int
    {
        $slug = Str::slug($name) ?: 'classe';
        $existing = DB::table('school_classes')->where('slug', $slug)->first();

        if ($existing) {
            return (int) $existing->id;
        }

        return (int) DB::table('school_classes')->insertGetId([
            'name' => trim($name),
            'slug' => $slug,
            'description' => 'Créée automatiquement depuis Import TD rapide.',
            'level' => 'secondaire',
            'order' => 999,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function ensureSubject(string $name): int
    {
        $slug = Str::slug($name) ?: 'matiere';
        $existing = DB::table('subjects')->where('slug', $slug)->first();

        if ($existing) {
            return (int) $existing->id;
        }

        return (int) DB::table('subjects')->insertGetId([
            'name' => trim($name),
            'slug' => $slug,
            'description' => 'Créée automatiquement depuis Import TD rapide.',
            'icon' => '📘',
            'color' => '#2563eb',
            'order' => 999,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function findAssignment(int $classId, int $subjectId): ?object
    {
        if (!Schema::hasTable('teacher_assignments')) {
            return null;
        }

        return DB::table('teacher_assignments')
            ->where('school_class_id', $classId)
            ->where('subject_id', $subjectId)
            ->where('is_active', true)
            ->orderByDesc('id')
            ->first();
    }

    protected function toHtml(string $text): string
    {
        $paragraphs = preg_split('/\n{2,}/', trim($text)) ?: [];

        return collect($paragraphs)
            ->map(fn ($p) => '<p>'.nl2br(e(trim($p))).'</p>')
            ->implode("\n");
    }
}
