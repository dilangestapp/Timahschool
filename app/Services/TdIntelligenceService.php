<?php

namespace App\Services;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TdSet;
use App\Models\TdSource;
use App\Models\TdTransformation;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TdIntelligenceService
{
    public function analyze(TdSource $source): TdSource
    {
        $text = $source->working_text;
        $subjects = Subject::query()->orderBy('name')->get();
        $classes = SchoolClass::query()->orderBy('name')->get();

        $detectedSubject = $this->matchSubject($text, $subjects);
        $detectedClass = $this->matchClass($text, $classes);
        $chapter = $this->detectChapter($text, $source->title);
        $difficulty = $this->detectDifficulty($text);
        $structure = $this->detectStructure($text);

        $source->update([
            'detected_subject_id' => $detectedSubject?->id,
            'detected_school_class_id' => $detectedClass?->id,
            'detected_chapter_label' => $chapter,
            'detected_difficulty' => $difficulty,
            'detected_structure_json' => $structure,
            'analysis_notes' => $this->buildAnalysisNotes($detectedClass?->name, $detectedSubject?->name, $chapter, $difficulty, $structure),
            'status' => TdSource::STATUS_ANALYZED,
            'last_analyzed_at' => now(),
        ]);

        return $source->fresh(['detectedSchoolClass', 'detectedSubject']);
    }

    public function createTransformation(
        TdSource $source,
        TeacherAssignment $assignment,
        User $author,
        string $variantType,
        ?string $notes = null,
        string $accessLevel = TdSet::ACCESS_FREE,
        string $targetStatus = TdSet::STATUS_DRAFT
    ): TdTransformation {
        if ($source->status === TdSource::STATUS_IMPORTED) {
            $source = $this->analyze($source);
        }

        $seed = $source->working_text;
        $segments = $this->extractSegments($seed);
        $chapter = $source->detected_chapter_label ?: ($source->title ?: 'Thème à préciser');
        $difficulty = $source->detected_difficulty ?: 'medium';
        $title = $this->buildTitle($assignment, $chapter, $variantType);
        $summary = $this->buildSummary($assignment, $chapter, $variantType, $notes);
        $instructions = $this->buildInstructionsHtml($assignment, $chapter, $variantType, $segments, $notes);
        $correction = $this->buildCorrectionHtml($assignment, $chapter, $variantType, $segments);

        $transformation = TdTransformation::query()->create([
            'td_source_id' => $source->id,
            'author_user_id' => $author->id,
            'variant_type' => $variantType,
            'generation_notes' => $notes,
            'transformed_title' => $title,
            'transformed_summary' => $summary,
            'transformed_instructions_html' => $instructions,
            'transformed_correction_html' => $correction,
            'transformed_chapter_label' => $chapter,
            'transformed_difficulty' => $difficulty,
            'transformed_structure_json' => $source->detected_structure_json ?? [],
            'status' => TdTransformation::STATUS_DRAFT,
        ]);

        $tdSet = TdSet::query()->create([
            'school_class_id' => $assignment->school_class_id,
            'subject_id' => $assignment->subject_id,
            'teacher_assignment_id' => $assignment->id,
            'author_user_id' => $author->id,
            'td_source_id' => $source->id,
            'td_transformation_id' => $transformation->id,
            'title' => $title,
            'slug' => Str::slug($title) . '-' . now()->timestamp,
            'chapter_label' => $chapter,
            'summary' => $summary,
            'instructions_html' => $instructions,
            'correction_html' => $correction,
            'difficulty' => $difficulty,
            'estimated_minutes' => ($source->detected_structure_json['estimated_minutes'] ?? 45),
            'access_level' => $accessLevel,
            'td_type' => 'training',
            'status' => $targetStatus,
            'correction_mode' => TdSet::CORRECTION_AFTER_SUBMIT,
            'source_type' => 'transformed_source',
            'source_label' => $source->source_label ?: ($source->source_url ?: $source->title),
            'license_type' => 'internal_transformation',
            'rights_confirmed' => true,
            'generation_mode' => 'semi_automatic',
            'submitted_at' => $targetStatus === TdSet::STATUS_SUBMITTED ? now() : null,
        ]);

        $transformation->update([
            'td_set_id' => $tdSet->id,
            'status' => TdTransformation::STATUS_SYNCED,
        ]);

        $source->update([
            'status' => TdSource::STATUS_TRANSFORMED,
        ]);

        return $transformation->fresh(['tdSet', 'source']);
    }

    protected function matchSubject(string $text, Collection $subjects): ?Subject
    {
        $text = mb_strtolower($text);

        return $subjects->first(function (Subject $subject) use ($text) {
            return str_contains($text, mb_strtolower($subject->name));
        });
    }

    protected function matchClass(string $text, Collection $classes): ?SchoolClass
    {
        $text = mb_strtolower($text);

        return $classes->first(function (SchoolClass $class) use ($text) {
            return str_contains($text, mb_strtolower($class->name));
        });
    }

    protected function detectChapter(string $text, ?string $fallbackTitle = null): string
    {
        if (preg_match('/(?:chapitre|th[eè]me|theme|le[çc]on)\s*[:\-]?\s*([^\n\r]+)/iu', $text, $matches)) {
            return trim(Str::limit($matches[1], 120, ''));
        }

        return trim((string) ($fallbackTitle ?: 'Thème de révision'));
    }

    protected function detectDifficulty(string $text): string
    {
        $text = mb_strtolower($text);

        if (str_contains($text, 'bac') || str_contains($text, 'probatoire') || str_contains($text, 'examen')) {
            return 'exam';
        }

        if (str_contains($text, 'initiation') || str_contains($text, 'notions de base') || str_contains($text, 'rappel')) {
            return 'easy';
        }

        return 'medium';
    }

    protected function detectStructure(string $text): array
    {
        $exerciseCount = preg_match_all('/\b(exercice|question|partie)\b/iu', $text, $matches);
        $keywords = collect(preg_split('/[^\pL\pN]+/u', mb_strtolower($text)))
            ->filter(fn ($word) => mb_strlen($word) >= 5)
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(12)
            ->values()
            ->all();

        return [
            'estimated_exercise_count' => max(3, min(8, $exerciseCount ?: 3)),
            'estimated_minutes' => min(120, max(30, ($exerciseCount ?: 3) * 15)),
            'keywords' => $keywords,
            'question_modes' => [
                'open',
                str_contains(mb_strtolower($text), 'justifier') ? 'reasoning' : 'application',
                str_contains(mb_strtolower($text), 'calcul') ? 'calculation' : 'analysis',
            ],
        ];
    }

    protected function buildAnalysisNotes(?string $className, ?string $subjectName, string $chapter, string $difficulty, array $structure): string
    {
        return implode("\n", array_filter([
            'Classe détectée : ' . ($className ?: 'non identifiée'),
            'Matière détectée : ' . ($subjectName ?: 'non identifiée'),
            'Chapitre pressenti : ' . $chapter,
            'Difficulté estimée : ' . $difficulty,
            'Volume d’exercices estimé : ' . ($structure['estimated_exercise_count'] ?? '-'),
            !empty($structure['keywords']) ? 'Mots-clés : ' . implode(', ', array_slice($structure['keywords'], 0, 6)) : null,
        ]));
    }

    protected function extractSegments(string $text): array
    {
        $segments = preg_split('/(?:\r?\n){2,}/', strip_tags($text));
        $segments = collect($segments)
            ->map(fn ($item) => trim(preg_replace('/\s+/', ' ', $item)))
            ->filter(fn ($item) => $item !== '')
            ->take(6)
            ->values()
            ->all();

        if (count($segments) >= 3) {
            return $segments;
        }

        return [
            'Rappeler les notions essentielles du chapitre et illustrer leur utilisation sur un cas simple.',
            'Résoudre un exercice d’application contextualisé en modifiant les données du sujet source.',
            'Traiter un exercice de synthèse qui exige une justification complète de la démarche.',
        ];
    }

    protected function buildTitle(TeacherAssignment $assignment, string $chapter, string $variantType): string
    {
        $prefix = match ($variantType) {
            'easier' => 'TD progressif',
            'harder' => 'TD approfondi',
            'inverted' => 'TD inversé',
            'fresh' => 'Nouvelle évaluation',
            default => 'TD retravaillé',
        };

        return $prefix . ' - ' . ($assignment->subject->name ?? 'Matière') . ' - ' . $chapter;
    }

    protected function buildSummary(TeacherAssignment $assignment, string $chapter, string $variantType, ?string $notes): string
    {
        $variantText = match ($variantType) {
            'easier' => 'version allégée pour renforcer les bases',
            'harder' => 'version renforcée avec des exigences plus élevées',
            'inverted' => 'version reformulée avec une logique inversée',
            'fresh' => 'nouvelle évaluation inspirée d’une source existante',
            default => 'version reformulée à partir d’une source pédagogique',
        };

        $base = 'TD pour ' . ($assignment->schoolClass->name ?? 'la classe') . ' en ' . ($assignment->subject->name ?? 'matière') . ', ' . $variantText . ', sur le chapitre « ' . $chapter . ' ». '; 

        return trim($base . ($notes ? 'Consigne complémentaire : ' . Str::limit($notes, 220) : ''));
    }

    protected function buildInstructionsHtml(TeacherAssignment $assignment, string $chapter, string $variantType, array $segments, ?string $notes): string
    {
        $intro = match ($variantType) {
            'easier' => 'Traitez les exercices du plus simple au plus guidé. Montrez chaque étape importante.',
            'harder' => 'Traitez les exercices avec une rédaction rigoureuse. Les justifications sont attendues.',
            'inverted' => 'Commencez par analyser les résultats attendus avant de reconstruire la démarche complète.',
            'fresh' => 'Travaillez comme sur une mini-évaluation inédite. Lisez bien les consignes avant de commencer.',
            default => 'Travaillez le TD comme une préparation sérieuse à l’examen. Soignez la rédaction et les unités.',
        };

        $exerciseBlocks = collect($segments)->take(3)->values()->map(function ($segment, $index) use ($variantType) {
            $title = 'Exercice ' . ($index + 1);
            $extra = match ($variantType) {
                'easier' => 'Commencez par reformuler la question avec vos propres mots avant de répondre.',
                'harder' => 'Ajoutez une justification complète et vérifiez la cohérence de votre résultat final.',
                'inverted' => 'Expliquez d’abord ce qu’il faut obtenir, puis reconstituez la méthode.',
                'fresh' => 'Utilisez le même chapitre, mais avec un contexte et des données différentes.',
                default => 'Présentez clairement la démarche retenue.',
            };

            return '<section><h3>' . e($title) . '</h3><p>' . e($segment) . '</p><p><em>' . e($extra) . '</em></p></section>';
        })->implode('');

        $notesBlock = $notes ? '<div class="td-ai-notes"><strong>Consigne de transformation :</strong> ' . e($notes) . '</div>' : '';

        return '<div class="td-rich-generated">'
            . '<p><strong>Chapitre :</strong> ' . e($chapter) . '</p>'
            . '<p>' . e($intro) . '</p>'
            . $notesBlock
            . $exerciseBlocks
            . '<section><h3>Consigne finale</h3><p>Relisez vos réponses, encadrez les résultats importants et soignez la présentation.</p></section>'
            . '</div>';
    }

    protected function buildCorrectionHtml(TeacherAssignment $assignment, string $chapter, string $variantType, array $segments): string
    {
        $blocks = collect($segments)->take(3)->values()->map(function ($segment, $index) use ($variantType) {
            $approach = match ($variantType) {
                'easier' => 'Commencer par rappeler la règle du cours, identifier les données, puis appliquer la méthode pas à pas.',
                'harder' => 'Justifier la méthode choisie, expliciter les hypothèses et vérifier la cohérence du résultat.',
                'inverted' => 'Partir du résultat attendu, expliquer les conditions de validité, puis dérouler la procédure.',
                'fresh' => 'Repérer l’objectif, choisir l’outil adapté, résoudre puis interpréter le résultat dans le nouveau contexte.',
                default => 'Identifier les données utiles, appliquer correctement le chapitre puis conclure clairement.',
            };

            return '<section><h3>Corrigé exercice ' . ($index + 1) . '</h3><p><strong>Démarche attendue :</strong> ' . e($approach) . '</p><p><em>Point d’attention :</em> ' . e(Str::limit($segment, 180)) . '</p></section>';
        })->implode('');

        return '<div class="td-rich-generated">'
            . '<p><strong>Corrigé indicatif</strong> — chapitre « ' . e($chapter) . ' ».</p>'
            . $blocks
            . '<section><h3>Astuce examen</h3><p>Commencez toujours par repérer ce qui est demandé, annoncez la méthode et vérifiez la cohérence de la conclusion.</p></section>'
            . '</div>';
    }
}
