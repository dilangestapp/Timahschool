<?php

namespace App\Services;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TdSet;
use App\Models\TdSource;
use App\Models\TdTransformation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TdPreparationService
{
    public function normalizeImportedText(TdSource $source): TdSource
    {
        $text = trim((string) $source->raw_text);

        if ($source->source_file_path && $source->source_file_mime === 'text/plain') {
            $path = storage_path('app/' . $source->source_file_path);
            if (is_file($path)) {
                $fileText = trim((string) @file_get_contents($path));
                if ($fileText !== '') {
                    $text = trim($text . "\n\n" . $fileText);
                }
            }
        }

        $classes = SchoolClass::query()->orderBy('name')->get();
        $subjects = Subject::query()->orderBy('name')->get();
        $detectedClass = $source->detectedSchoolClass ?: $this->guessClass($text, $classes);
        $detectedSubject = $source->detectedSubject ?: $this->guessSubject($text, $subjects);
        $chapter = $source->detected_chapter_label ?: $this->guessChapter($text, $source->title);
        $difficulty = $source->detected_difficulty ?: $this->guessDifficulty($text);
        $structure = $this->extractExercises($text);

        $analysis = [
            'classe' => $detectedClass?->name,
            'matiere' => $detectedSubject?->name,
            'chapitre' => $chapter,
            'difficulte' => $difficulty,
            'exercices_detectes' => count($structure),
            'visuels_detectes' => $source->visuals()->count(),
            'note' => 'Préparation semi-manuelle pour ChatGPT sans API. Vérifier la pertinence pédagogique avant collage final.',
        ];

        $source->update([
            'extracted_text' => $text,
            'detected_school_class_id' => $detectedClass?->id,
            'detected_subject_id' => $detectedSubject?->id,
            'detected_chapter_label' => $chapter,
            'detected_difficulty' => $difficulty,
            'detected_structure_json' => $structure,
            'analysis_notes' => $this->formatAnalysis($analysis),
        ]);

        return $source->fresh(['visuals', 'detectedSchoolClass', 'detectedSubject']);
    }

    public function preparePrompt(TdSource $source): TdSource
    {
        $source = $this->normalizeImportedText($source);

        $visuals = $source->visuals->map(function ($visual) {
            return [
                'nom' => $visual->file_name,
                'role' => $visual->visual_role,
                'exercice' => $visual->exercise_label,
                'notes' => $visual->notes,
            ];
        })->values()->all();

        $package = [
            'titre_source' => $source->title,
            'matiere' => $source->detectedSubject?->name,
            'classe' => $source->detectedSchoolClass?->name,
            'chapitre' => $source->detected_chapter_label,
            'difficulte' => $source->detected_difficulty,
            'exercices_detectes' => $source->detected_structure_json ?? [],
            'visuels' => $visuals,
            'texte' => $source->extracted_text,
        ];

        $prompt = $this->buildPrompt($package);

        $source->update([
            'prompt_ready_text' => $prompt,
            'prompt_package_json' => $package,
            'status' => TdSource::STATUS_PREPARED,
            'prepared_at' => now(),
        ]);

        return $source->fresh(['visuals', 'detectedSchoolClass', 'detectedSubject']);
    }

    public function createDraftFromPreparedResult(TdSource $source, array $payload, int $authorId): TdTransformation
    {
        $generatedStructure = [
            'title' => $payload['generated_title'] ?? $source->title,
            'chapter' => $source->detected_chapter_label,
            'difficulty' => $source->detected_difficulty,
            'visuals_to_keep' => $source->visuals()->whereIn('visual_role', ['essential', 'useful'])->count(),
        ];

        $transformation = TdTransformation::query()->create([
            'td_source_id' => $source->id,
            'author_user_id' => $authorId,
            'variant_type' => $payload['variant_type'] ?? 'chatgpt_reworked',
            'generation_notes' => $payload['generation_notes'] ?? null,
            'prompt_snapshot' => $source->prompt_ready_text,
            'generated_title' => $payload['generated_title'],
            'generated_summary' => $payload['generated_summary'] ?? null,
            'generated_instructions_html' => $payload['generated_instructions_html'],
            'generated_correction_html' => $payload['generated_correction_html'] ?? null,
            'generated_structure_json' => $generatedStructure,
            'status' => TdTransformation::STATUS_IMPORTED,
        ]);

        $tdSet = TdSet::query()->create([
            'school_class_id' => $source->detected_school_class_id,
            'subject_id' => $source->detected_subject_id,
            'teacher_assignment_id' => $source->teacher_assignment_id,
            'author_user_id' => $authorId,
            'td_source_id' => $source->id,
            'td_transformation_id' => $transformation->id,
            'title' => $payload['generated_title'],
            'slug' => Str::slug($payload['generated_title']) . '-' . now()->timestamp,
            'chapter_label' => $source->detected_chapter_label,
            'summary' => $payload['generated_summary'] ?? null,
            'instructions_html' => $payload['generated_instructions_html'],
            'correction_html' => $payload['generated_correction_html'] ?? null,
            'difficulty' => $source->detected_difficulty ?: 'medium',
            'estimated_minutes' => $payload['estimated_minutes'] ?? null,
            'access_level' => $payload['access_level'] ?? TdSet::ACCESS_FREE,
            'td_type' => $payload['td_type'] ?? 'training',
            'status' => $payload['status'] ?? TdSet::STATUS_DRAFT,
            'correction_mode' => $payload['correction_mode'] ?? TdSet::CORRECTION_AFTER_SUBMIT,
            'source_type' => 'chatgpt_prepared',
            'source_label' => $source->source_label ?: $source->title,
            'license_type' => 'à vérifier',
            'rights_confirmed' => (bool) $source->rights_confirmed,
            'generation_mode' => 'prepare_for_chatgpt',
            'submitted_at' => ($payload['status'] ?? TdSet::STATUS_DRAFT) === TdSet::STATUS_SUBMITTED ? now() : null,
            'published_at' => ($payload['status'] ?? TdSet::STATUS_DRAFT) === TdSet::STATUS_PUBLISHED ? now() : null,
        ]);

        $transformation->update(['td_set_id' => $tdSet->id]);
        $source->update(['status' => TdSource::STATUS_TRANSFORMED]);

        return $transformation->fresh(['tdSet', 'source']);
    }

    protected function guessClass(string $text, Collection $classes): ?SchoolClass
    {
        $lower = mb_strtolower($text);
        foreach ($classes as $class) {
            if ($class->name && str_contains($lower, mb_strtolower($class->name))) {
                return $class;
            }
        }
        return null;
    }

    protected function guessSubject(string $text, Collection $subjects): ?Subject
    {
        $lower = mb_strtolower($text);
        foreach ($subjects as $subject) {
            if ($subject->name && str_contains($lower, mb_strtolower($subject->name))) {
                return $subject;
            }
        }
        return null;
    }

    protected function guessChapter(string $text, ?string $title): ?string
    {
        foreach (preg_split('/\n+/', trim($text)) as $line) {
            $line = trim($line);
            if ($line !== '' && mb_strlen($line) <= 120 && preg_match('/(chapitre|theme|thème|lecon|leçon)/iu', $line)) {
                return $line;
            }
        }
        return $title ?: null;
    }

    protected function guessDifficulty(string $text): string
    {
        $lower = mb_strtolower($text);
        if (str_contains($lower, 'probatoire') || str_contains($lower, 'baccalauréat') || str_contains($lower, 'bac') || str_contains($lower, 'examen')) {
            return 'exam';
        }
        return 'medium';
    }

    protected function extractExercises(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        $parts = preg_split('/(?=(?:^|\n)\s*(?:exercice|exercise|question)\s*\d+)/iu', $text);
        $items = [];
        foreach ($parts as $index => $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            $lines = preg_split('/\n+/', $part);
            $label = trim($lines[0] ?? ('Exercice ' . ($index + 1)));
            $items[] = [
                'label' => $label,
                'excerpt' => Str::limit(strip_tags($part), 180),
            ];
        }

        if ($items === []) {
            $items[] = [
                'label' => 'Bloc unique',
                'excerpt' => Str::limit(strip_tags($text), 180),
            ];
        }

        return $items;
    }

    protected function formatAnalysis(array $analysis): string
    {
        $lines = [];
        foreach ($analysis as $key => $value) {
            $lines[] = ucfirst(str_replace('_', ' ', $key)) . ' : ' . ($value === null || $value === '' ? '-' : $value);
        }
        return implode("\n", $lines);
    }

    protected function buildPrompt(array $package): string
    {
        $visualLines = [];
        foreach ($package['visuels'] as $visual) {
            $visualLines[] = '- ' . ($visual['nom'] ?? 'visuel')
                . ' | rôle : ' . ($visual['role'] ?? 'useful')
                . ' | exercice : ' . ($visual['exercice'] ?: 'non précisé')
                . (!empty($visual['notes']) ? ' | notes : ' . $visual['notes'] : '');
        }

        $exerciseLines = [];
        foreach ($package['exercices_detectes'] as $exercise) {
            $exerciseLines[] = '- ' . ($exercise['label'] ?? 'Exercice') . ' : ' . ($exercise['excerpt'] ?? '');
        }

        $intro = "Tu es chargé de produire un nouveau TD original à partir d'un sujet source. Tu ne dois pas copier ni paraphraser de manière superficielle. Reformule réellement, change les contextes, les données, l'ordre si nécessaire, et garde une qualité pédagogique forte.\n\n";
        $intro .= "Contexte pédagogique :\n";
        $intro .= "- Matière : " . ($package['matiere'] ?? '-') . "\n";
        $intro .= "- Classe : " . ($package['classe'] ?? '-') . "\n";
        $intro .= "- Chapitre : " . ($package['chapitre'] ?? '-') . "\n";
        $intro .= "- Difficulté visée : " . ($package['difficulte'] ?? '-') . "\n\n";
        $intro .= "Exercices détectés dans la source :\n";

        return trim(
            $intro . implode("\n", $exerciseLines) . "\n\n"
            . "Visuels extraits à conserver si pédagogiquement utiles :\n" . (implode("\n", $visualLines) ?: '- Aucun visuel extrait') . "\n\n"
            . "Texte source nettoyé :\n" . ($package['texte'] ?? '') . "\n\n"
            . "Travail demandé :\n"
            . "1. Proposer un titre de TD clair.\n"
            . "2. Produire un résumé court.\n"
            . "3. Produire l'énoncé complet du TD en HTML simple (paragraphes, listes, titres si nécessaire).\n"
            . "4. Produire le corrigé détaillé en HTML simple.\n"
            . "5. Conserver explicitement les visuels essentiels ou utiles en indiquant à quel exercice ils doivent rester attachés.\n"
            . "6. Éviter toute copie brute du texte source.\n\n"
            . "Réponds sous cette structure :\n"
            . "TITRE:\n...\n\nRESUME:\n...\n\nTD_HTML:\n...\n\nCORRIGE_HTML:\n...\n\nVISUELS_A_CONSERVER:\n...\n"
        );
    }
}
