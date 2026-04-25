<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TdQuestionThread;
use App\Models\TdSet;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminTdController extends Controller
{
    public function index(Request $request)
    {
        $query = TdSet::query()->with(['schoolClass', 'subject', 'author', 'assignment.teacher']);

        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }
        if ($request->filled('access_level')) {
            $query->where('access_level', (string) $request->string('access_level'));
        }
        if ($request->filled('q')) {
            $term = trim((string) $request->string('q'));
            $query->where(function ($builder) use ($term) {
                $builder->where('title', 'like', "%{$term}%")
                    ->orWhere('chapter_label', 'like', "%{$term}%")
                    ->orWhere('document_name', 'like', "%{$term}%");
            });
        }

        return view('admin.td.index', [
            'sets' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only('status', 'access_level', 'q'),
            'assignments' => $this->assignments(),
            'questionCount' => Schema::hasTable('td_question_threads') ? TdQuestionThread::query()->count() : 0,
            'openQuestionCount' => Schema::hasTable('td_question_threads') ? TdQuestionThread::query()->where('status', TdQuestionThread::STATUS_OPEN)->count() : 0,
        ]);
    }

    public function create()
    {
        return view('admin.td.create', [
            'td' => new TdSet([
                'difficulty' => 'medium',
                'access_level' => 'free',
                'status' => TdSet::STATUS_DRAFT,
                'correction_delay_minutes' => 30,
            ]),
            'assignments' => $this->assignments(),
            'action' => route('admin.td.store'),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $assignment = $this->assignmentOrFail((int) $data['teacher_assignment_id']);

        [$documentPath, $documentName, $documentMime, $documentSize] = $this->storeFile($request, 'document', 'td/documents');
        [$correctionPath, $correctionName, $correctionMime, $correctionSize] = $this->storeFile($request, 'correction_document', 'td/corrections');

        TdSet::query()->create([
            'school_class_id' => $assignment->school_class_id,
            'subject_id' => $assignment->subject_id,
            'teacher_assignment_id' => $assignment->id,
            'author_user_id' => $assignment->teacher_id,
            'title' => $data['title'],
            'slug' => Str::slug($data['title']) . '-' . now()->timestamp,
            'chapter_label' => $data['chapter_label'] ?? null,
            'difficulty' => $data['difficulty'],
            'access_level' => $data['access_level'],
            'correction_delay_minutes' => (int) ($data['correction_delay_minutes'] ?? 30),
            'status' => $data['status'],
            'document_path' => $documentPath,
            'document_name' => $documentName,
            'document_mime' => $documentMime,
            'document_size' => $documentSize,
            'editable_html' => $data['editable_html'] ?: null,
            'editable_text' => $data['editable_text'] ?: null,
            'has_editable_version' => !empty($data['editable_html']),
            'correction_html' => $data['correction_html'] ?: null,
            'correction_document_path' => $correctionPath,
            'correction_document_name' => $correctionName,
            'correction_document_mime' => $correctionMime,
            'correction_document_size' => $correctionSize,
            'published_at' => $data['status'] === TdSet::STATUS_PUBLISHED ? now() : null,
        ]);

        return redirect()->route('admin.td.index')->with('success', 'TD créé avec succès.');
    }

    public function edit(TdSet $td)
    {
        return view('admin.td.edit', [
            'td' => $td->load(['assignment.teacher', 'schoolClass', 'subject']),
            'assignments' => $this->assignments(),
            'action' => route('admin.td.update', $td),
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, TdSet $td)
    {
        $data = $this->validated($request);
        $assignment = $this->assignmentOrFail((int) $data['teacher_assignment_id']);

        $updates = [
            'school_class_id' => $assignment->school_class_id,
            'subject_id' => $assignment->subject_id,
            'teacher_assignment_id' => $assignment->id,
            'author_user_id' => $assignment->teacher_id,
            'title' => $data['title'],
            'chapter_label' => $data['chapter_label'] ?? null,
            'difficulty' => $data['difficulty'],
            'access_level' => $data['access_level'],
            'correction_delay_minutes' => (int) ($data['correction_delay_minutes'] ?? 30),
            'status' => $data['status'],
            'editable_html' => $data['editable_html'] ?: null,
            'editable_text' => $data['editable_text'] ?: null,
            'has_editable_version' => !empty($data['editable_html']),
            'correction_html' => $data['correction_html'] ?: null,
            'published_at' => $data['status'] === TdSet::STATUS_PUBLISHED ? ($td->published_at ?? now()) : null,
        ];

        if ($request->boolean('remove_document') && $td->document_path) {
            $this->deleteFile($td->document_path);
            $updates['document_path'] = null;
            $updates['document_name'] = null;
            $updates['document_mime'] = null;
            $updates['document_size'] = null;
        }

        if ($request->boolean('remove_correction_document') && $td->correction_document_path) {
            $this->deleteFile($td->correction_document_path);
            $updates['correction_document_path'] = null;
            $updates['correction_document_name'] = null;
            $updates['correction_document_mime'] = null;
            $updates['correction_document_size'] = null;
        }

        [$documentPath, $documentName, $documentMime, $documentSize] = $this->storeFile($request, 'document', 'td/documents');
        if ($documentPath) {
            if ($td->document_path) {
                $this->deleteFile($td->document_path);
            }
            $updates['document_path'] = $documentPath;
            $updates['document_name'] = $documentName;
            $updates['document_mime'] = $documentMime;
            $updates['document_size'] = $documentSize;
        }

        [$correctionPath, $correctionName, $correctionMime, $correctionSize] = $this->storeFile($request, 'correction_document', 'td/corrections');
        if ($correctionPath) {
            if ($td->correction_document_path) {
                $this->deleteFile($td->correction_document_path);
            }
            $updates['correction_document_path'] = $correctionPath;
            $updates['correction_document_name'] = $correctionName;
            $updates['correction_document_mime'] = $correctionMime;
            $updates['correction_document_size'] = $correctionSize;
        }

        $td->update($updates);

        return redirect()->route('admin.td.index')->with('success', 'TD mis à jour avec succès.');
    }

    public function publish(TdSet $td)
    {
        $td->update([
            'status' => TdSet::STATUS_PUBLISHED,
            'published_at' => $td->published_at ?? now(),
        ]);

        return back()->with('success', 'TD publié.');
    }

    public function archive(TdSet $td)
    {
        $td->update(['status' => TdSet::STATUS_ARCHIVED]);
        return back()->with('success', 'TD désactivé temporairement.');
    }

    public function delete(TdSet $td)
    {
        if ($td->document_path) {
            $this->deleteFile($td->document_path);
        }
        if ($td->correction_document_path) {
            $this->deleteFile($td->correction_document_path);
        }
        $td->delete();

        return back()->with('success', 'TD supprimé définitivement.');
    }

    public function document(TdSet $td)
    {
        return $this->showStoredFile($td->document_path, $td->document_name ?: 'document-td');
    }

    public function correctionDocument(TdSet $td)
    {
        return $this->showStoredFile($td->correction_document_path, $td->correction_document_name ?: 'corrige-td');
    }

    protected function assignments()
    {
        if (!class_exists(TeacherAssignment::class)) {
            return collect();
        }

        return TeacherAssignment::query()
            ->with(['teacher', 'schoolClass', 'subject'])
            ->where('is_active', true)
            ->orderByDesc('id')
            ->get();
    }

    protected function assignmentOrFail(int $id): TeacherAssignment
    {
        return TeacherAssignment::query()
            ->with(['teacher', 'schoolClass', 'subject'])
            ->where('is_active', true)
            ->findOrFail($id);
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'teacher_assignment_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'chapter_label' => ['nullable', 'string', 'max:255'],
            'difficulty' => ['required', 'in:easy,medium,hard,exam'],
            'access_level' => ['required', 'in:free,premium'],
            'correction_delay_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'status' => ['required', 'in:draft,published,archived'],
            'document' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx,txt,odt,rtf,html,htm'],
            'editable_html' => ['nullable', 'string'],
            'editable_text' => ['nullable', 'string'],
            'correction_html' => ['nullable', 'string'],
            'correction_document' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx,txt,odt,rtf,html,htm'],
            'remove_document' => ['nullable', 'boolean'],
            'remove_correction_document' => ['nullable', 'boolean'],
        ]);
    }

    protected function storeFile(Request $request, string $field, string $dir): array
    {
        if (!$request->hasFile($field)) {
            return [null, null, null, null];
        }

        $file = $request->file($field);
        $path = $file->store($dir, 'public');

        return [$path, $file->getClientOriginalName(), $file->getMimeType(), $file->getSize()];
    }

    protected function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function showStoredFile(?string $path, string $fallbackName)
    {
        abort_if(!$path || !Storage::disk('public')->exists($path), 404);

        $absolutePath = Storage::disk('public')->path($path);
        $mime = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';

        return Response::file($absolutePath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . addslashes($fallbackName) . '"',
        ]);
    }
}
