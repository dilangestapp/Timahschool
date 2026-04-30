<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DirectTdImportController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $secret = (string) env('TIMAH_IMPORT_SECRET', 'timah2026');
        $given = (string) ($request->header('X-TIMAH-IMPORT-KEY') ?: $request->input('key'));

        if ($secret === '' || !hash_equals($secret, $given)) {
            return response()->json([
                'status' => 'forbidden',
                'message' => 'Clé d’import invalide.',
            ], 403);
        }

        $data = $request->validate([
            'classe' => ['nullable', 'string', 'max:255'],
            'class' => ['nullable', 'string', 'max:255'],
            'matiere' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'titre' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'statut' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'string', 'max:40'],
            'contenu_td' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'corrige' => ['nullable', 'string'],
            'correction' => ['nullable', 'string'],
            'difficulty' => ['nullable', 'in:easy,medium,hard,exam'],
            'access_level' => ['nullable', 'in:free,premium'],
            'chapter_label' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'import_block' => ['nullable', 'string'],
            'key' => ['nullable', 'string'],
        ]);

        $payload = $this->normalizePayload($data);

        if (!$payload['classe'] || !$payload['matiere'] || !$payload['titre'] || !$payload['contenu_td']) {
            return response()->json([
                'status' => 'invalid_payload',
                'message' => 'Import impossible : classe, matière, titre ou contenu TD manquant.',
                'detected' => $payload,
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($payload) {
                $now = now();
                $classId = $this->ensureClass($payload['classe']);
                $subjectId = $this->ensureSubject($payload['matiere']);
                $assignment = $this->findAssignment($classId, $subjectId);
                $authorId = $assignment?->teacher_id ?: $this->fallbackAuthorId();

                $status = $this->normalizeStatus($payload['statut']);
                $title = trim($payload['titre']);
                $slugBase = Str::slug($title) ?: 'td-importe';
                $slug = $this->uniqueSlug($slugBase);

                $tdId = DB::table('td_sets')->insertGetId([
                    'school_class_id' => $classId,
                    'subject_id' => $subjectId,
                    'teacher_assignment_id' => $assignment?->id,
                    'author_user_id' => $authorId,
                    'title' => $title,
                    'slug' => $slug,
                    'chapter_label' => $payload['chapter_label'] ?: null,
                    'summary' => Schema::hasColumn('td_sets', 'summary') ? Str::limit(strip_tags($payload['contenu_td']), 220) : null,
                    'instructions_html' => Schema::hasColumn('td_sets', 'instructions_html') ? $this->toHtml($payload['contenu_td']) : null,
                    'correction_html' => $payload['corrige'] ? $this->toHtml($payload['corrige']) : null,
                    'difficulty' => $payload['difficulty'] ?: 'medium',
                    'estimated_minutes' => Schema::hasColumn('td_sets', 'estimated_minutes') ? null : null,
                    'access_level' => $payload['access_level'] ?: 'free',
                    'td_type' => Schema::hasColumn('td_sets', 'td_type') ? 'training' : null,
                    'status' => $status,
                    'correction_mode' => Schema::hasColumn('td_sets', 'correction_mode') ? 'after_submit' : null,
                    'source_type' => Schema::hasColumn('td_sets', 'source_type') ? 'direct_import' : null,
                    'source_label' => Schema::hasColumn('td_sets', 'source_label') ? ($payload['source'] ?: 'Import direct ChatGPT') : null,
                    'rights_confirmed' => Schema::hasColumn('td_sets', 'rights_confirmed') ? true : null,
                    'editable_html' => Schema::hasColumn('td_sets', 'editable_html') ? $this->toHtml($payload['contenu_td']) : null,
                    'editable_text' => Schema::hasColumn('td_sets', 'editable_text') ? $payload['contenu_td'] : null,
                    'has_editable_version' => Schema::hasColumn('td_sets', 'has_editable_version') ? true : null,
                    'correction_delay_minutes' => Schema::hasColumn('td_sets', 'correction_delay_minutes') ? 30 : null,
                    'published_at' => $status === 'published' ? $now : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                return [
                    'td_id' => $tdId,
                    'title' => $title,
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'teacher_assignment_id' => $assignment?->id,
                    'author_user_id' => $authorId,
                    'status' => $status,
                ];
            });

            return response()->json([
                'status' => 'done',
                'message' => 'TD importé avec succès dans TIMAH ACADEMY.',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    private function normalizePayload(array $data): array
    {
        if (!empty($data['import_block'])) {
            $parsed = $this->parseImportBlock((string) $data['import_block']);
            $data = array_merge($parsed, $data);
        }

        return [
            'classe' => trim((string) ($data['classe'] ?? $data['class'] ?? '')),
            'matiere' => trim((string) ($data['matiere'] ?? $data['subject'] ?? '')),
            'titre' => trim((string) ($data['titre'] ?? $data['title'] ?? '')),
            'statut' => trim((string) ($data['statut'] ?? $data['status'] ?? 'published')),
            'contenu_td' => trim((string) ($data['contenu_td'] ?? $data['content'] ?? '')),
            'corrige' => trim((string) ($data['corrige'] ?? $data['correction'] ?? '')),
            'difficulty' => $data['difficulty'] ?? 'medium',
            'access_level' => $data['access_level'] ?? 'free',
            'chapter_label' => trim((string) ($data['chapter_label'] ?? '')),
            'source' => trim((string) ($data['source'] ?? 'Import direct ChatGPT')),
        ];
    }

    private function parseImportBlock(string $raw): array
    {
        $text = trim(str_replace(["\r\n", "\r"], "\n", $raw));

        return [
            'classe' => $this->firstValue($text, ['CLASSE', 'CLASS', 'NIVEAU']),
            'matiere' => $this->firstValue($text, ['MATIÈRE', 'MATIERE', 'SUBJECT']),
            'titre' => $this->firstValue($text, ['TITRE', 'TITLE', 'SUJET']),
            'statut' => $this->firstValue($text, ['STATUT', 'STATUS']) ?: 'published',
            'chapter_label' => $this->firstValue($text, ['CHAPITRE', 'THÈME', 'THEME']),
            'contenu_td' => $this->section($text, ['CONTENU_TD', 'CONTENU TD', 'TD', 'SUJET_TD'], ['CORRIGE', 'CORRIGÉ', 'CORRECTION', 'FIN_IMPORT_TIMAH_ACADEMY']),
            'corrige' => $this->section($text, ['CORRIGE', 'CORRIGÉ', 'CORRECTION'], ['FIN_IMPORT_TIMAH_ACADEMY']),
        ];
    }

    private function firstValue(string $text, array $labels): ?string
    {
        foreach ($labels as $label) {
            $pattern = '/^\s*'.preg_quote($label, '/').'\s*[:：-]\s*(.+)$/imu';
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    private function section(string $text, array $starts, array $ends): string
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

    private function normalizeStatus(?string $status): string
    {
        $status = Str::lower(trim((string) $status));
        return in_array($status, ['publie', 'publié', 'published', 'public', 'publication'], true) ? 'published' : 'draft';
    }

    private function ensureClass(string $name): int
    {
        $slug = Str::slug($name) ?: 'classe';
        $existing = DB::table('school_classes')->where('slug', $slug)->first();

        if ($existing) {
            return (int) $existing->id;
        }

        return (int) DB::table('school_classes')->insertGetId([
            'name' => trim($name),
            'slug' => $slug,
            'description' => 'Créée automatiquement depuis import direct TD.',
            'level' => 'secondaire',
            'order' => 999,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureSubject(string $name): int
    {
        $slug = Str::slug($name) ?: 'matiere';
        $existing = DB::table('subjects')->where('slug', $slug)->first();

        if ($existing) {
            return (int) $existing->id;
        }

        return (int) DB::table('subjects')->insertGetId([
            'name' => trim($name),
            'slug' => $slug,
            'description' => 'Créée automatiquement depuis import direct TD.',
            'icon' => '📘',
            'color' => '#2563eb',
            'order' => 999,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function findAssignment(int $classId, int $subjectId): ?object
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

    private function fallbackAuthorId(): int
    {
        $adminRoleId = Schema::hasTable('roles') ? DB::table('roles')->where('name', 'admin')->value('id') : null;

        if ($adminRoleId && Schema::hasTable('role_user')) {
            $userId = DB::table('role_user')->where('role_id', $adminRoleId)->value('user_id');
            if ($userId) {
                return (int) $userId;
            }
        }

        $firstUser = DB::table('users')->orderBy('id')->value('id');
        if ($firstUser) {
            return (int) $firstUser;
        }

        return (int) DB::table('users')->insertGetId([
            'name' => 'Administration TIMAH ACADEMY',
            'username' => Schema::hasColumn('users', 'username') ? 'admin_import' : null,
            'full_name' => Schema::hasColumn('users', 'full_name') ? 'Administration TIMAH ACADEMY' : null,
            'email' => 'admin.import@timahacademy.cm',
            'phone' => Schema::hasColumn('users', 'phone') ? '692762065' : null,
            'status' => Schema::hasColumn('users', 'status') ? 'active' : null,
            'password' => Hash::make(Str::random(24)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $i = 1;

        while (DB::table('td_sets')->where('slug', $slug)->exists()) {
            $slug = $base.'-'.now()->format('YmdHis').'-'.$i;
            $i++;
        }

        return $slug;
    }

    private function toHtml(string $text): string
    {
        $paragraphs = preg_split('/\n{2,}/', trim($text)) ?: [];

        return collect($paragraphs)
            ->map(fn ($p) => '<p>'.nl2br(e(trim($p))).'</p>')
            ->implode("\n");
    }
}
