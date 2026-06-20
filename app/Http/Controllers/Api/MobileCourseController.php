<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class MobileCourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->mobileUser($request);
        if (!$user) {
            return $this->unauthenticated();
        }

        if (!Schema::hasTable('courses')) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Le module Cours n’est pas encore initialisé.',
                'items' => [],
                'subjects' => [],
            ]);
        }

        $user->loadMissing('studentProfile.schoolClass');
        $classId = $user->studentProfile?->school_class_id;
        if (!$classId) {
            return response()->json([
                'status' => 'class_required',
                'message' => 'Aucune classe n’est liée à ce compte élève.',
                'items' => [],
                'subjects' => [],
            ], 403);
        }

        $term = trim((string) $request->query('q', ''));
        $subjectId = (int) $request->query('subject_id', 0);

        $items = Course::query()
            ->with(['subject', 'schoolClass', 'creator'])
            ->visibleOnMobile()
            ->where('school_class_id', $classId)
            ->when($subjectId > 0, fn ($query) => $query->where('subject_id', $subjectId))
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($sub) use ($term) {
                    $sub->where('title', 'like', '%' . $term . '%')
                        ->orWhere('description', 'like', '%' . $term . '%')
                        ->orWhere('objectives', 'like', '%' . $term . '%')
                        ->orWhere('document_name', 'like', '%' . $term . '%');

                    if (Schema::hasColumn('courses', 'content_text')) {
                        $sub->orWhere('content_text', 'like', '%' . $term . '%');
                    }
                });
            })
            ->orderByDesc('published_at')
            ->orderBy('order')
            ->orderByDesc('id')
            ->take(80)
            ->get();

        $subjectIds = Course::query()
            ->visibleOnMobile()
            ->where('school_class_id', $classId)
            ->pluck('subject_id')
            ->filter()
            ->unique()
            ->values();

        $subjects = Schema::hasTable('subjects')
            ? Subject::query()->whereIn('id', $subjectIds)->orderBy('name')->get()->map(fn ($subject) => [
                'id' => $subject->id,
                'name' => $subject->name,
                'color' => $subject->color ?? null,
                'icon' => $subject->icon ?? null,
            ])->values()
            : collect();

        return response()->json([
            'status' => 'ok',
            'message' => $items->isEmpty() ? 'Aucun cours publié pour votre classe pour le moment.' : 'Cours chargés.',
            'class' => [
                'id' => $user->studentProfile?->schoolClass?->id,
                'name' => $user->studentProfile?->schoolClass?->name,
            ],
            'subjects' => $subjects,
            'items' => $items->map(fn (Course $course) => $this->serializeCourse($course))->values(),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $this->mobileUser($request);
        if (!$user) {
            return $this->unauthenticated();
        }

        $user->loadMissing('studentProfile.schoolClass');
        $classId = $user->studentProfile?->school_class_id;
        $course = Course::query()->with(['subject', 'schoolClass', 'creator'])->find($id);

        if (!$course || !$classId || !$this->canOpen($course, (int) $classId)) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Cours introuvable ou non disponible pour votre classe.',
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Cours chargé.',
            'item' => $this->serializeCourse($course, true),
        ]);
    }

    public function document(Request $request, int $id): Response|JsonResponse
    {
        return $this->serveDocument($request, $id, false);
    }

    public function download(Request $request, int $id): Response|JsonResponse
    {
        return $this->serveDocument($request, $id, true);
    }

    private function serveDocument(Request $request, int $id, bool $download): Response|JsonResponse
    {
        $user = $this->mobileUser($request);
        if (!$user) {
            return $this->unauthenticated();
        }

        $user->loadMissing('studentProfile.schoolClass');
        $classId = $user->studentProfile?->school_class_id;
        $course = Course::query()->find($id);

        if (!$course || !$classId || !$this->canOpen($course, (int) $classId)) {
            return response()->json(['status' => 'not_found', 'message' => 'Document introuvable.'], 404);
        }

        if ($download && !$course->isDownloadable()) {
            return response()->json(['status' => 'forbidden', 'message' => 'Le téléchargement de ce cours n’est pas autorisé.'], 403);
        }

        if (!$course->document_path) {
            return response()->json(['status' => 'not_found', 'message' => 'Aucun document n’est attaché à ce cours.'], 404);
        }

        $path = storage_path('app/' . $course->document_path);
        if (!file_exists($path)) {
            return response()->json(['status' => 'not_found', 'message' => 'Le fichier du cours est introuvable sur le serveur.'], 404);
        }

        if ($download) {
            return response()->download($path, $course->document_name ?: basename($path));
        }

        return response()->file($path, ['Content-Type' => $course->document_mime ?: 'application/octet-stream']);
    }

    private function canOpen(Course $course, int $classId): bool
    {
        return (int) $course->school_class_id === $classId
            && $course->status === Course::STATUS_PUBLISHED
            && $course->mobileAccess() !== Course::MOBILE_ACCESS_LOCKED;
    }

    private function serializeCourse(Course $course, bool $full = false): array
    {
        $hasDocument = $course->hasDocument();
        $canDownload = $hasDocument && $course->isDownloadable();
        $publishedAt = $course->published_at;

        $data = [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'description' => $course->description,
            'objectives' => $course->objectives,
            'excerpt' => $course->excerpt(220),
            'subject_id' => $course->subject_id,
            'subject' => $course->subject?->name,
            'class_id' => $course->school_class_id,
            'class' => $course->schoolClass?->name,
            'teacher' => $course->creator?->name ?? $course->creator?->full_name,
            'level' => $course->level,
            'order' => (int) ($course->order ?? 0),
            'status' => $course->status,
            'mobile_access' => $course->mobileAccess(),
            'estimated_minutes' => Schema::hasColumn('courses', 'estimated_minutes') ? $course->estimated_minutes : null,
            'published_at' => $publishedAt?->toIso8601String(),
            'published_label' => $publishedAt ? 'Publié le ' . $publishedAt->format('d/m/Y à H:i') : 'Publication immédiate',
            'has_rich_content' => $course->hasRichContent(),
            'has_document' => $hasDocument,
            'is_downloadable' => $canDownload,
            'document_name' => $course->document_name,
            'document_mime' => $course->document_mime,
            'document_size' => $course->document_size,
            'document_size_label' => $course->humanDocumentSize(),
            'document_url' => $hasDocument ? url('/api/mobile/courses/' . $course->id . '/document') : null,
            'download_url' => $canDownload ? url('/api/mobile/courses/' . $course->id . '/download') : null,
        ];

        if ($full) {
            $data['content_html'] = $course->content_html ?? null;
            $data['content_text'] = $course->content_text ?? null;
            $data['reading_hint'] = $hasDocument
                ? 'Lis le contenu du cours puis ouvre le document joint si nécessaire.'
                : 'Lis attentivement le cours et note les notions importantes dans ton cahier.';
        }

        return $data;
    }

    private function mobileUser(Request $request): ?User
    {
        $token = (string) $request->bearerToken();
        if ($token === '') {
            return null;
        }

        return User::query()->where('remember_token', hash('sha256', $token))->first();
    }

    private function unauthenticated(): JsonResponse
    {
        return response()->json([
            'status' => 'unauthenticated',
            'message' => 'Session mobile expirée. Veuillez vous reconnecter.',
        ], 401);
    }
}
