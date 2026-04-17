<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TdSet;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TdSetController extends Controller
{
    public function index(Request $request)
    {
        $assignments = $this->assignments()->keyBy('id');
        $query = TdSet::query()->with(['schoolClass', 'subject', 'assignment']);
        $this->applyAssignments($query, $assignments->values());

        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }

        if ($request->filled('q')) {
            $term = trim((string) $request->string('q'));
            $query->where(function ($builder) use ($term) {
                $builder->where('title', 'like', "%{$term}%")
                    ->orWhere('chapter_label', 'like', "%{$term}%")
                    ->orWhere('document_name', 'like', "%{$term}%");
            });
        }

        $sets = $query->latest()->paginate(12)->withQueryString();

        return view('teacher.td.sets.index', [
            'sets' => $sets,
            'assignments' => $assignments->values(),
            'filters' => $request->only('status', 'q'),
        ]);
    }

    public function create()
    {
        return view('teacher.td.sets.create', [
            'assignments' => $this->assignments(),
            'td' => new TdSet([
                'difficulty' => 'medium',
                'access_level' => 'free',
                'status' => 'draft',
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $assignments = $this->assignments()->keyBy('id');
        $data = $this->validated($request, true);

        abort_unless($assignments->has((int) $data['teacher_assignment_id']), 403);
        $assignment = $assignments[(int) $data['teacher_assignment_id']];

        [$documentPath, $documentName, $documentMime, $documentSize] = $this->storeFile($request, 'document', 'td/documents');
        [$correctionPath, $correctionName, $correctionMime, $correctionSize] = $this->storeFile($request, 'correction_document', 'td/corrections');

        TdSet::query()->create([
            'school_class_id' => $assignment->school_class_id,
            'subject_id' => $assignment->subject_id,
            'teacher_assignment_id' => $assignment->id,
            'author_user_id' => auth()->id(),
            'title' => $data['title'],
            'slug' => Str::slug($data['title']) . '-' . now()->timestamp,
            'chapter_label' => $data['chapter_label'] ?? null,
            'difficulty' => $data['difficulty'],
            'access_level' => $data['access_level'],
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

        return redirect()->route('teacher.td.sets.index')->with('success', 'TD enregistré avec succès.');
    }

    public function edit(TdSet $td)
    {
        $this->authorizeTd($td);

        return view('teacher.td.sets.edit', [
            'td' => $td->load(['schoolClass', 'subject', 'assignment']),
            'assignments' => $this->assignments(),
        ]);
    }

    public function update(Request $request, TdSet $td)
    {
        $this->authorizeTd($td);
        $data = $this->validated($request, false);

        $updates = [
            'title' => $data['title'],
            'chapter_label' => $data['chapter_label'] ?? null,
            'difficulty' => $data['difficulty'],
            'access_level' => $data['access_level'],
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

        return redirect()->route('teacher.td.sets.index')->with('success', 'TD mis à jour avec succès.');
    }

    public function publish(TdSet $td)
    {
        $this->authorizeTd($td);
        $td->update([
            'status' => TdSet::STATUS_PUBLISHED,
            'published_at' => $td->published_at ?? now(),
        ]);

        return back()->with('success', 'TD publié.');
    }

    public function archive(TdSet $td)
    {
        $this->authorizeTd($td);
        $td->update(['status' => TdSet::STATUS_ARCHIVED]);

        return back()->with('success', 'TD archivé.');
    }

    public function destroy(TdSet $td)
    {
        $this->authorizeTd($td);

        if ($td->document_path) {
            $this->deleteFile($td->document_path);
        }
        if ($td->correction_document_path) {
            $this->deleteFile($td->correction_document_path);
        }
        $td->delete();

        return back()->with('success', 'TD supprimé.');
    }

    public function document(TdSet $td)
    {
        $this->authorizeTd($td);
        abort_unless($td->document_path, 404);
        return Storage::disk('public')->response($td->document_path, $td->document_name ?: basename($td->document_path));
    }

    public function correctionDocument(TdSet $td)
    {
        $this->authorizeTd($td);
        abort_unless($td->correction_document_path, 404);
        return Storage::disk('public')->response($td->correction_document_path, $td->correction_document_name ?: basename($td->correction_document_path));
    }

    protected function validated(Request $request, bool $withAssignment): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'chapter_label' => ['nullable', 'string', 'max:255'],
            'difficulty' => ['required', 'in:easy,medium,hard,exam'],
            'access_level' => ['required', 'in:free,premium'],
            'status' => ['required', 'in:draft,published,archived'],
            'document' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx,txt,odt,rtf,html,htm,png,jpg,jpeg,webp'],
            'editable_html' => ['nullable', 'string'],
            'editable_text' => ['nullable', 'string'],
            'correction_html' => ['nullable', 'string'],
            'correction_document' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx,txt,odt,rtf,html,htm,png,jpg,jpeg,webp'],
            'remove_document' => ['nullable', 'boolean'],
            'remove_correction_document' => ['nullable', 'boolean'],
        ];

        if ($withAssignment) {
            $rules['teacher_assignment_id'] = ['required', 'integer'];
        }

        return $request->validate($rules, [
            'document.mimes' => 'Formats TD autorisés : PDF, DOC, DOCX, TXT, ODT, RTF, HTML, PNG, JPG, JPEG, WEBP.',
            'correction_document.mimes' => 'Formats corrigé autorisés : PDF, DOC, DOCX, TXT, ODT, RTF, HTML, PNG, JPG, JPEG, WEBP.',
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

    protected function authorizeTd(TdSet $td): void
    {
        $assignments = $this->assignments();
        $allowed = $assignments->first(function ($assignment) use ($td) {
            return (int) $assignment->school_class_id === (int) $td->school_class_id
                && (int) $assignment->subject_id === (int) $td->subject_id;
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
}
