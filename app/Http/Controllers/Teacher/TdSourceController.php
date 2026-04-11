<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TdSource;
use App\Models\TeacherAssignment;
use App\Services\TdIntelligenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TdSourceController extends Controller
{
    public function index(Request $request)
    {
        $query = TdSource::query()->with(['teacherAssignment.schoolClass', 'teacherAssignment.subject', 'detectedSubject', 'detectedSchoolClass', 'transformations.tdSet'])
            ->where('uploaded_by', auth()->id());

        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }

        if ($request->filled('source_kind')) {
            $query->where('source_kind', (string) $request->string('source_kind'));
        }

        if ($request->filled('q')) {
            $term = trim((string) $request->string('q'));
            $query->where(function ($builder) use ($term) {
                $builder->where('title', 'like', "%{$term}%")
                    ->orWhere('source_label', 'like', "%{$term}%")
                    ->orWhere('analysis_notes', 'like', "%{$term}%");
            });
        }

        return view('teacher.td.sources.index', [
            'sources' => $query->latest()->paginate(12)->withQueryString(),
            'filters' => $request->only('status', 'source_kind', 'q'),
            'assignments' => $this->assignments(),
        ]);
    }

    public function create()
    {
        return view('teacher.td.sources.create', [
            'assignments' => $this->assignments(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'teacher_assignment_id' => ['required', 'integer'],
            'source_kind' => ['required', 'in:url,text,prompt,pdf,image,document,legacy_td'],
            'title' => ['nullable', 'string', 'max:255'],
            'source_url' => ['nullable', 'url', 'max:1000'],
            'source_label' => ['nullable', 'string', 'max:255'],
            'prompt_text' => ['nullable', 'string'],
            'raw_text' => ['nullable', 'string'],
            'source_file' => ['nullable', 'file', 'max:25600', 'mimes:pdf,doc,docx,png,jpg,jpeg,txt,rtf,odt'],
            'rights_confirmed' => ['nullable', 'accepted'],
        ], [
            'rights_confirmed.accepted' => 'Vous devez confirmer que la source peut être utilisée pour produire un nouveau TD.',
            'source_file.mimes' => 'Fichiers autorisés : PDF, DOC, DOCX, PNG, JPG, TXT, RTF, ODT.',
        ]);

        $assignment = $this->resolveAssignment((int) $data['teacher_assignment_id']);

        if (empty($data['source_url']) && empty($data['prompt_text']) && empty($data['raw_text']) && !$request->hasFile('source_file')) {
            return back()->withInput()->withErrors([
                'raw_text' => 'Fournissez au moins un lien, un texte, un prompt ou un document source.',
            ]);
        }

        [$filePath, $fileName, $fileMime, $fileSize, $seedText] = $this->storeSourceFile($request);

        $source = TdSource::query()->create([
            'teacher_assignment_id' => $assignment->id,
            'uploaded_by' => auth()->id(),
            'source_kind' => $data['source_kind'],
            'title' => $data['title'] ?? null,
            'source_url' => $data['source_url'] ?? null,
            'source_label' => $data['source_label'] ?? null,
            'prompt_text' => $data['prompt_text'] ?? null,
            'raw_text' => $data['raw_text'] ?? null,
            'extracted_text' => $seedText,
            'source_file_path' => $filePath,
            'source_file_name' => $fileName,
            'source_file_mime' => $fileMime,
            'source_file_size' => $fileSize,
            'status' => TdSource::STATUS_IMPORTED,
            'rights_confirmed' => (bool) ($data['rights_confirmed'] ?? false),
        ]);

        return redirect()->route('teacher.td.sources.show', $source)->with('success', 'Source TD importée avec succès.');
    }

    public function show(TdSource $tdSource)
    {
        $this->authorizeSource($tdSource);

        return view('teacher.td.sources.show', [
            'source' => $tdSource->load([
                'teacherAssignment.schoolClass',
                'teacherAssignment.subject',
                'detectedSubject',
                'detectedSchoolClass',
                'transformations.tdSet',
            ]),
            'assignments' => $this->assignments(),
        ]);
    }

    public function analyze(TdSource $tdSource, TdIntelligenceService $service)
    {
        $this->authorizeSource($tdSource);
        $service->analyze($tdSource);

        return back()->with('success', 'Analyse terminée. Le chapitre, la matière et la structure proposée ont été détectés.');
    }

    public function generate(Request $request, TdSource $tdSource, TdIntelligenceService $service)
    {
        $this->authorizeSource($tdSource);

        $data = $request->validate([
            'variant_type' => ['required', 'in:similar,easier,harder,inverted,fresh'],
            'generation_notes' => ['nullable', 'string', 'max:4000'],
            'teacher_assignment_id' => ['nullable', 'integer'],
            'access_level' => ['required', 'in:free,premium'],
            'target_status' => ['required', 'in:draft,submitted'],
        ]);

        $assignment = !empty($data['teacher_assignment_id'])
            ? $this->resolveAssignment((int) $data['teacher_assignment_id'])
            : $this->resolveAssignment((int) $tdSource->teacher_assignment_id);

        $transformation = $service->createTransformation(
            $tdSource,
            $assignment,
            auth()->user(),
            $data['variant_type'],
            $data['generation_notes'] ?? null,
            $data['access_level'],
            $data['target_status']
        );

        return redirect()->route('teacher.td.sets.edit', $transformation->tdSet)->with('success', 'Brouillon TD généré à partir de la source. Vérifiez-le puis enregistrez.');
    }

    public function file(TdSource $tdSource)
    {
        $this->authorizeSource($tdSource);
        abort_unless($tdSource->source_file_path, 404);
        $path = storage_path('app/' . $tdSource->source_file_path);
        abort_unless(file_exists($path), 404);

        if (str_starts_with((string) $tdSource->source_file_mime, 'image/') || $tdSource->source_file_mime === 'application/pdf') {
            return response()->file($path, ['Content-Type' => $tdSource->source_file_mime]);
        }

        return response()->download($path, $tdSource->source_file_name ?: basename($path));
    }

    protected function authorizeSource(TdSource $tdSource): void
    {
        abort_unless((int) $tdSource->uploaded_by === (int) auth()->id(), 403);
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

    protected function resolveAssignment(int $assignmentId): TeacherAssignment
    {
        $assignment = $this->assignments()->firstWhere('id', $assignmentId);
        abort_unless($assignment, 403);

        return $assignment;
    }

    protected function storeSourceFile(Request $request): array
    {
        if (!$request->hasFile('source_file')) {
            return [null, null, null, null, null];
        }

        $file = $request->file('source_file');
        $original = $file->getClientOriginalName();
        $storedName = now()->format('YmdHis') . '_' . Str::slug(pathinfo($original, PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('td-sources/files', $storedName);

        $seedText = null;
        if (in_array($file->getMimeType(), ['text/plain', 'application/rtf'], true)) {
            try {
                $seedText = trim((string) Storage::get($path));
            } catch (\Throwable $e) {
                $seedText = null;
            }
        }

        return [$path, $original, $file->getMimeType(), $file->getSize(), $seedText];
    }
}
