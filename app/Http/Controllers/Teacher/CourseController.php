<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\TeacherAssignment;
use App\Support\CoursePublicationNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $assignments = $this->assignments();
        $baseQuery = Course::query()
            ->with(['schoolClass', 'subject', 'creator'])
            ->where('created_by', auth()->id());
        $this->applyAssignments($baseQuery, $assignments);

        $statusCounters = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $query = clone $baseQuery;

        $status = trim((string) $request->get('status', ''));
        if ($status !== '') {
            $query->where('status', $status);
        }

        $term = trim((string) $request->get('q', ''));
        if ($term !== '') {
            $query->where(function ($builder) use ($term) {
                $builder->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('objectives', 'like', "%{$term}%")
                    ->orWhere('document_name', 'like', "%{$term}%");

                if (Schema::hasColumn('courses', 'content_text')) {
                    $builder->orWhere('content_text', 'like', "%{$term}%");
                } elseif (Schema::hasColumn('courses', 'content_html')) {
                    $builder->orWhere('content_html', 'like', "%{$term}%");
                }
            });
        }

        $courses = $query->latest()->paginate(12)->withQueryString();

        return view('teacher.courses.index', [
            'courses' => $courses,
            'assignments' => $assignments,
            'filters' => $request->only('status', 'q'),
            'statusCounters' => [
                'draft' => (int) ($statusCounters[Course::STATUS_DRAFT] ?? 0),
                'published' => (int) ($statusCounters[Course::STATUS_PUBLISHED] ?? 0),
                'archived' => (int) ($statusCounters[Course::STATUS_ARCHIVED] ?? 0),
                'total' => (int) $statusCounters->sum(),
            ],
        ]);
    }

    public function create()
    {
        return view('teacher.courses.create', [
            'assignments' => $this->assignments(),
        ]);
    }

    public function store(Request $request, CoursePublicationNotifier $notifier)
    {
        $assignments = $this->assignments()->keyBy('id');

        $data = $request->validate([
            'teacher_assignment_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'objectives' => ['nullable', 'string'],
            'content_html' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:draft,published,archived'],
            'action_mode' => ['nullable', 'in:save_private,publish_now,archive_now'],
            'document' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx,ppt,pptx,txt,rtf,odt'],
        ], [
            'document.mimes' => 'Formats autorisés : PDF, DOC, DOCX, PPT, PPTX, TXT, RTF, ODT.',
            'document.max' => 'Le document ne doit pas dépasser 20 Mo.',
        ]);

        abort_unless($assignments->has((int) $data['teacher_assignment_id']), 403);
        $assignment = $assignments[(int) $data['teacher_assignment_id']];

        [$documentPath, $documentName, $documentMime, $documentSize] = $this->storeDocument($request);
        [$contentHtml, $contentText] = $this->normalizeContent($data['content_html'] ?? null);
        $status = $this->statusFromAction($request, $data['status'] ?? Course::STATUS_DRAFT);

        if (!$documentPath && !$contentHtml) {
            return back()->withInput()->withErrors([
                'content_html' => 'Ajoutez soit un contenu rédigé, soit un document joint, ou les deux. Le cours peut rester en brouillon dans votre espace.',
            ]);
        }

        $payload = [
            'subject_id' => $assignment->subject_id,
            'school_class_id' => $assignment->school_class_id,
            'created_by' => auth()->id(),
            'title' => $data['title'],
            'slug' => Str::slug($data['title']) . '-' . now()->timestamp,
            'description' => $data['description'] ?? null,
            'objectives' => $data['objectives'] ?? null,
            'level' => $assignment->schoolClass->name ?? null,
            'order' => $data['order'] ?? 0,
            'status' => $status,
            'published_at' => $status === Course::STATUS_PUBLISHED ? now() : null,
            'document_path' => $documentPath,
            'document_name' => $documentName,
            'document_mime' => $documentMime,
            'document_size' => $documentSize,
        ];

        if (Schema::hasColumn('courses', 'content_html')) {
            $payload['content_html'] = $contentHtml;
        }
        if (Schema::hasColumn('courses', 'content_text')) {
            $payload['content_text'] = $contentText;
        }

        $course = Course::query()->create($payload);
        if ($course->status === Course::STATUS_PUBLISHED) {
            $notifier->coursePublished($course->fresh(['subject', 'schoolClass', 'creator']), auth()->user());
        }

        if ($course->status === Course::STATUS_DRAFT) {
            return redirect()
                ->route('teacher.courses.index', ['status' => Course::STATUS_DRAFT])
                ->with('success', 'Cours gardé dans votre espace personnel. Il reste invisible pour les élèves jusqu’à publication.');
        }

        return redirect()->route('teacher.courses.index')->with('success', 'Cours enregistré avec succès. Les notifications internes ont été préparées si le cours est publié.');
    }

    public function edit(Course $course)
    {
        $this->authorizeCourse($course);

        return view('teacher.courses.edit', [
            'course' => $course->load(['schoolClass', 'subject']),
            'assignments' => $this->assignments(),
        ]);
    }

    public function update(Request $request, Course $course, CoursePublicationNotifier $notifier)
    {
        $this->authorizeCourse($course);
        $wasPublished = $course->status === Course::STATUS_PUBLISHED;

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'objectives' => ['nullable', 'string'],
            'content_html' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:draft,published,archived'],
            'action_mode' => ['nullable', 'in:save_private,publish_now,archive_now'],
            'document' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx,ppt,pptx,txt,rtf,odt'],
            'remove_document' => ['nullable', 'boolean'],
        ], [
            'document.mimes' => 'Formats autorisés : PDF, DOC, DOCX, PPT, PPTX, TXT, RTF, ODT.',
            'document.max' => 'Le document ne doit pas dépasser 20 Mo.',
        ]);

        [$contentHtml, $contentText] = $this->normalizeContent($data['content_html'] ?? null);
        $status = $this->statusFromAction($request, $data['status'] ?? $course->status ?? Course::STATUS_DRAFT);

        $updates = [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'objectives' => $data['objectives'] ?? null,
            'order' => $data['order'] ?? 0,
            'status' => $status,
            'published_at' => $status === Course::STATUS_PUBLISHED ? ($course->published_at ?? now()) : null,
        ];

        if (Schema::hasColumn('courses', 'content_html')) {
            $updates['content_html'] = $contentHtml;
        }
        if (Schema::hasColumn('courses', 'content_text')) {
            $updates['content_text'] = $contentText;
        }

        if ($request->boolean('remove_document') && $course->document_path) {
            $this->deleteDocument($course->document_path);
            $updates['document_path'] = null;
            $updates['document_name'] = null;
            $updates['document_mime'] = null;
            $updates['document_size'] = null;
        }

        if ($request->hasFile('document')) {
            if ($course->document_path) {
                $this->deleteDocument($course->document_path);
            }

            [$documentPath, $documentName, $documentMime, $documentSize] = $this->storeDocument($request);
            $updates['document_path'] = $documentPath;
            $updates['document_name'] = $documentName;
            $updates['document_mime'] = $documentMime;
            $updates['document_size'] = $documentSize;
        }

        $futureDocumentPath = $updates['document_path'] ?? $course->document_path;
        $futureContentHtml = $updates['content_html'] ?? ($course->content_html ?? null);

        if (!$futureDocumentPath && !$futureContentHtml) {
            return back()->withInput()->withErrors([
                'content_html' => 'Ajoutez soit un contenu rédigé, soit un document joint, ou les deux. Le cours peut rester en brouillon dans votre espace.',
            ]);
        }

        $course->update($updates);
        if (!$wasPublished && $course->status === Course::STATUS_PUBLISHED) {
            $notifier->coursePublished($course->fresh(['subject', 'schoolClass', 'creator']), auth()->user());
        }

        if ($course->status === Course::STATUS_DRAFT) {
            return redirect()
                ->route('teacher.courses.index', ['status' => Course::STATUS_DRAFT])
                ->with('success', 'Cours conservé dans votre espace personnel. Il reste invisible pour les élèves jusqu’à publication.');
        }

        return redirect()->route('teacher.courses.index')->with('success', 'Cours mis à jour avec succès.');
    }

    public function publish(Course $course, CoursePublicationNotifier $notifier)
    {
        $this->authorizeCourse($course);
        $wasPublished = $course->status === Course::STATUS_PUBLISHED;
        $course->update([
            'status' => Course::STATUS_PUBLISHED,
            'published_at' => $course->published_at ?? now(),
        ]);

        if (!$wasPublished) {
            $notifier->coursePublished($course->fresh(['subject', 'schoolClass', 'creator']), auth()->user());
        }

        return redirect()->route('teacher.courses.index')->with('success', 'Cours publié. Les élèves et les parents liés recevront une notification interne.');
    }

    public function archive(Course $course)
    {
        $this->authorizeCourse($course);
        $course->update(['status' => Course::STATUS_ARCHIVED]);

        return redirect()->route('teacher.courses.index')->with('success', 'Cours archivé.');
    }

    public function destroy(Course $course)
    {
        $this->authorizeCourse($course);

        if ($course->document_path) {
            $this->deleteDocument($course->document_path);
        }

        $course->delete();

        return redirect()->route('teacher.courses.index')->with('success', 'Cours supprimé.');
    }

    public function document(Course $course)
    {
        $this->authorizeCourse($course);
        abort_unless($course->document_path, 404);

        $path = storage_path('app/' . $course->document_path);
        abort_unless(file_exists($path), 404);

        return response()->file($path, [
            'Content-Type' => $course->document_mime ?: 'application/octet-stream',
        ]);
    }

    public function downloadDocument(Course $course)
    {
        $this->authorizeCourse($course);
        abort_unless($course->document_path, 404);

        $path = storage_path('app/' . $course->document_path);
        abort_unless(file_exists($path), 404);

        return response()->download($path, $course->document_name ?: basename($path));
    }

    protected function authorizeCourse(Course $course): void
    {
        if (Schema::hasColumn('courses', 'created_by')) {
            abort_unless((int) $course->created_by === (int) auth()->id(), 403);
        }

        $assignments = $this->assignments();
        $allowed = $assignments->contains(function ($assignment) use ($course) {
            return (int) $assignment->school_class_id === (int) $course->school_class_id
                && (int) $assignment->subject_id === (int) $course->subject_id;
        });

        abort_unless($allowed, 403);
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

    protected function applyAssignments($query, Collection $assignments): void
    {
        if ($assignments->isEmpty()) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where(function ($builder) use ($assignments) {
            foreach ($assignments as $assignment) {
                $builder->orWhere(function ($inner) use ($assignment) {
                    $inner->where('school_class_id', $assignment->school_class_id)
                        ->where('subject_id', $assignment->subject_id);
                });
            }
        });
    }

    protected function storeDocument(Request $request): array
    {
        if (!$request->hasFile('document')) {
            return [null, null, null, null];
        }

        $file = $request->file('document');
        $original = $file->getClientOriginalName();
        $filename = now()->format('YmdHis') . '_' . Str::random(8) . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $original);
        $path = $file->storeAs('course_documents', $filename, 'local');

        return [$path, $original, $file->getMimeType(), $file->getSize()];
    }

    protected function deleteDocument(?string $path): void
    {
        if ($path && Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    protected function statusFromAction(Request $request, string $fallback): string
    {
        return match ($request->input('action_mode')) {
            'save_private' => Course::STATUS_DRAFT,
            'publish_now' => Course::STATUS_PUBLISHED,
            'archive_now' => Course::STATUS_ARCHIVED,
            default => in_array($fallback, [Course::STATUS_DRAFT, Course::STATUS_PUBLISHED, Course::STATUS_ARCHIVED], true) ? $fallback : Course::STATUS_DRAFT,
        };
    }

    protected function normalizeContent(?string $html): array
    {
        $html = trim((string) $html);

        if ($html === '') {
            return [null, null];
        }

        $html = preg_replace('/<(script|style)\b[^>]*>(.*?)<\/\1>/is', '', $html) ?? $html;
        $html = trim($html);
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? '');

        if ($text === '') {
            return [null, null];
        }

        return [$html, $text];
    }
}
