<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PedagogicalBankItem;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TdSet;
use App\Services\GoogleDriveStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminPedagogicalBankController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::query()->orderBy('order')->orderBy('name')->get();
        $subjects = Subject::query()->orderBy('order')->orderBy('name')->get();

        $classId = $request->integer('school_class_id') ?: null;
        $type = $request->input('content_type', 'td');
        $status = $request->input('status', 'available');
        $subjectId = $request->integer('subject_id') ?: null;

        $base = PedagogicalBankItem::query()
            ->with(['schoolClass', 'subject', 'lastTdSet'])
            ->when($classId, fn ($query) => $query->where('school_class_id', $classId))
            ->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId))
            ->when($type !== 'all', fn ($query) => $query->where('content_type', $type))
            ->latest('id');

        $availableItems = (clone $base)->where('status', PedagogicalBankItem::STATUS_AVAILABLE)->take(100)->get();
        $usedItems = (clone $base)->where('status', PedagogicalBankItem::STATUS_USED)->take(100)->get();
        $archivedItems = (clone $base)->where('status', PedagogicalBankItem::STATUS_ARCHIVED)->take(100)->get();

        if ($status === 'used') {
            $availableItems = collect();
            $archivedItems = collect();
        } elseif ($status === 'archived') {
            $availableItems = collect();
            $usedItems = collect();
        } elseif ($status === 'available') {
            $usedItems = collect();
            $archivedItems = collect();
        }

        return view('admin.pedagogical-bank.index', compact(
            'classes',
            'subjects',
            'availableItems',
            'usedItems',
            'archivedItems',
            'classId',
            'subjectId',
            'type',
            'status'
        ));
    }

    public function store(Request $request, GoogleDriveStorageService $drive)
    {
        $data = $request->validate([
            'school_class_id' => ['nullable', 'integer'],
            'subject_id' => ['nullable', 'integer'],
            'content_type' => ['required', 'string', 'max:40'],
            'title' => ['nullable', 'string', 'max:255'],
            'document' => ['required', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg', 'max:30720'],
            'correction_document' => ['nullable', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg', 'max:30720'],
        ]);

        $document = $request->file('document');
        $correction = $request->file('correction_document');
        $inferred = $this->inferFromFileName($document->getClientOriginalName());

        $payload = [
            'school_class_id' => $this->normalizeNullableId($data['school_class_id'] ?? null) ?: $this->resolveClassId($inferred['class_label']),
            'subject_id' => $this->normalizeNullableId($data['subject_id'] ?? null) ?: $this->resolveSubjectId($inferred['subject_label']),
            'created_by' => auth()->id(),
            'code' => $inferred['code'],
            'title' => $data['title'] ?: $inferred['title'],
            'content_type' => $data['content_type'],
            'inferred_class' => $inferred['class_label'],
            'inferred_subject' => $inferred['subject_label'],
            'theme' => $inferred['theme'],
            'status' => PedagogicalBankItem::STATUS_AVAILABLE,
        ];

        $payload = array_merge($payload, $this->storeUploadedFile($document, 'document', $drive));

        if ($correction) {
            $payload = array_merge($payload, $this->storeUploadedFile($correction, 'correction_document', $drive));
        }

        PedagogicalBankItem::query()->create($payload);

        return back()->with('success', 'Document ajouté à la banque pédagogique. Une copie Google Drive est créée si la configuration Drive est active.');
    }

    public function update(Request $request, PedagogicalBankItem $item, GoogleDriveStorageService $drive)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:80'],
            'content_type' => ['required', 'string', 'max:40'],
            'school_class_id' => ['nullable', 'integer'],
            'subject_id' => ['nullable', 'integer'],
            'theme' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:40'],
            'document' => ['nullable', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg', 'max:30720'],
            'correction_document' => ['nullable', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg', 'max:30720'],
        ]);

        $payload = [
            'title' => $data['title'],
            'code' => $data['code'] ?: null,
            'content_type' => $data['content_type'],
            'school_class_id' => $this->normalizeNullableId($data['school_class_id'] ?? null),
            'subject_id' => $this->normalizeNullableId($data['subject_id'] ?? null),
            'theme' => $data['theme'] ?: null,
            'status' => in_array($data['status'], [PedagogicalBankItem::STATUS_AVAILABLE, PedagogicalBankItem::STATUS_USED, PedagogicalBankItem::STATUS_ARCHIVED], true)
                ? $data['status']
                : PedagogicalBankItem::STATUS_AVAILABLE,
        ];

        if ($request->hasFile('document')) {
            $payload = array_merge($payload, $this->storeUploadedFile($request->file('document'), 'document', $drive));
        }

        if ($request->hasFile('correction_document')) {
            $payload = array_merge($payload, $this->storeUploadedFile($request->file('correction_document'), 'correction_document', $drive));
        }

        $item->update($payload);
        $this->syncLastTdSetFromBankItem($item->fresh());

        return back()->with('success', 'Fiche mise à jour. Les fichiers Drive sont synchronisés si la configuration Drive est active.');
    }

    public function schedule(Request $request, PedagogicalBankItem $item)
    {
        if ($item->content_type !== PedagogicalBankItem::TYPE_TD) {
            return back()->with('error', 'Pour la V1 test, la programmation automatique concerne d’abord les TD PDF.');
        }

        $data = $request->validate([
            'publish_at' => ['nullable', 'date'],
            'school_class_id' => ['nullable', 'integer'],
            'subject_id' => ['nullable', 'integer'],
            'correction_delay_minutes' => ['required', 'integer', 'min:0', 'max:1440'],
            'access_level' => ['required', 'string', 'max:40'],
        ]);

        $classId = $this->normalizeNullableId($data['school_class_id'] ?? null) ?: $item->school_class_id;
        $subjectId = $this->normalizeNullableId($data['subject_id'] ?? null) ?: $item->subject_id;

        if (!$classId || !$subjectId) {
            return back()->with('error', 'Classe ou matière manquante. Choisissez la classe et la matière dans la fiche du TD, puis sauvegardez.');
        }

        if (!$item->document_path && !$item->document_drive_url) {
            return back()->with('error', 'Document sujet manquant. Ajoutez ou remplacez le PDF du sujet avant de publier.');
        }

        $td = DB::transaction(function () use ($item, $data, $classId, $subjectId) {
            $item->forceFill([
                'school_class_id' => $classId,
                'subject_id' => $subjectId,
            ])->save();

            $td = TdSet::query()->create([
                'pedagogical_bank_item_id' => $item->id,
                'school_class_id' => $classId,
                'subject_id' => $subjectId,
                'author_user_id' => auth()->id(),
                'title' => $item->title,
                'slug' => Str::slug($item->title . '-' . $item->id . '-' . now()->timestamp),
                'chapter_label' => $item->theme,
                'difficulty' => 'standard',
                'access_level' => $data['access_level'],
                'correction_delay_minutes' => (int) $data['correction_delay_minutes'],
                'status' => TdSet::STATUS_PUBLISHED,
                'document_path' => $item->document_path,
                'document_drive_id' => $item->document_drive_id,
                'document_drive_url' => $item->document_drive_url,
                'document_name' => $item->document_name,
                'document_mime' => $item->document_mime,
                'document_size' => $item->document_size,
                'correction_document_path' => $item->correction_document_path,
                'correction_document_drive_id' => $item->correction_document_drive_id,
                'correction_document_drive_url' => $item->correction_document_drive_url,
                'correction_document_name' => $item->correction_document_name,
                'correction_document_mime' => $item->correction_document_mime,
                'correction_document_size' => $item->correction_document_size,
                'published_at' => $data['publish_at'] ? now()->parse($data['publish_at']) : now(),
            ]);

            $item->forceFill([
                'status' => PedagogicalBankItem::STATUS_USED,
                'times_used' => ((int) $item->times_used) + 1,
                'last_scheduled_at' => now(),
                'last_td_set_id' => $td->id,
            ])->save();

            return $td;
        });

        return back()->with('success', 'TD publié avec succès. Il est maintenant classé dans “Déjà utilisés”.');
    }

    public function archive(PedagogicalBankItem $item)
    {
        $item->update(['status' => PedagogicalBankItem::STATUS_ARCHIVED]);
        return back()->with('success', 'Document archivé.');
    }

    public function restore(PedagogicalBankItem $item)
    {
        $item->update(['status' => PedagogicalBankItem::STATUS_AVAILABLE]);
        return back()->with('success', 'Document remis dans les disponibles.');
    }

    private function storeUploadedFile($file, string $prefix, GoogleDriveStorageService $drive): array
    {
        $path = $file->store('pedagogical-bank', 'public');
        $payload = [
            $prefix . '_path' => $path,
            $prefix . '_name' => $file->getClientOriginalName(),
            $prefix . '_mime' => $file->getClientMimeType(),
            $prefix . '_size' => $file->getSize(),
        ];

        $drivePayload = $drive->upload($file, $prefix);
        if ($drivePayload) {
            $payload = array_merge($payload, $drivePayload);
        }

        return $payload;
    }

    private function inferFromFileName(string $filename): array
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $parts = explode('_', $name);
        $first = $parts[0] ?? '';
        $segments = explode('-', $first);

        $classCode = strtoupper($segments[0] ?? '');
        $subjectCode = strtoupper($segments[1] ?? '');
        $number = $segments[2] ?? null;
        $themeParts = array_values(array_filter(array_slice($parts, 3)));
        $theme = trim(str_replace(['-', '_'], ' ', implode(' ', $themeParts)));

        $classLabel = $this->classLabelFromCode($classCode);
        $subjectLabel = $this->subjectLabelFromCode($subjectCode);
        $code = trim($classCode . ($subjectCode ? '-' . $subjectCode : '') . ($number ? '-' . $number : ''), '-');
        $titleTheme = $theme ? Str::headline($theme) : 'Document pédagogique';
        $title = 'TD ' . ($classLabel ?: 'Classe') . ' — ' . ($subjectLabel ?: 'Matière') . ' : ' . $titleTheme;

        return [
            'code' => $code ?: null,
            'class_label' => $classLabel,
            'subject_label' => $subjectLabel,
            'theme' => $titleTheme,
            'title' => $title,
        ];
    }

    private function classLabelFromCode(string $code): ?string
    {
        return match ($code) {
            'PA' => 'Première A',
            'PA4' => 'Première A4 Allemand',
            'PC' => 'Première C',
            'PD' => 'Première D',
            'TA' => 'Terminale A',
            'TC' => 'Terminale C',
            'TD' => 'Terminale D',
            '3E', 'TROISIEME' => 'Troisième',
            default => null,
        };
    }

    private function subjectLabelFromCode(string $code): ?string
    {
        return match ($code) {
            'ALL' => 'Allemand',
            'FR' => 'Français',
            'ANG', 'EN' => 'Anglais',
            'MATH', 'MAT' => 'Mathématiques',
            'PCT' => 'PCT',
            'SVT' => 'SVT',
            'INFO' => 'Informatique',
            'PHILO' => 'Philosophie',
            default => null,
        };
    }

    private function resolveClassId(?string $label): ?int
    {
        if (!$label) {
            return null;
        }

        $normalized = Str::lower(Str::ascii($label));

        return SchoolClass::query()->get()->first(function ($class) use ($normalized, $label) {
            $name = (string) $class->name;
            $classNormalized = Str::lower(Str::ascii($name));
            return $classNormalized === $normalized
                || str_contains($classNormalized, $normalized)
                || str_contains($normalized, $classNormalized)
                || $name === $label;
        })?->id;
    }

    private function resolveSubjectId(?string $label): ?int
    {
        if (!$label) {
            return null;
        }

        $normalized = Str::lower(Str::ascii($label));

        return Subject::query()->get()->first(function ($subject) use ($normalized, $label) {
            $name = (string) $subject->name;
            $subjectNormalized = Str::lower(Str::ascii($name));
            return $subjectNormalized === $normalized
                || str_contains($subjectNormalized, $normalized)
                || str_contains($normalized, $subjectNormalized)
                || $name === $label;
        })?->id;
    }

    private function normalizeNullableId($value): ?int
    {
        $value = (int) ($value ?: 0);
        return $value > 0 ? $value : null;
    }

    private function syncLastTdSetFromBankItem(PedagogicalBankItem $item): void
    {
        if (!$item->last_td_set_id) {
            return;
        }

        $td = TdSet::query()->find($item->last_td_set_id);
        if (!$td) {
            return;
        }

        $td->forceFill([
            'school_class_id' => $item->school_class_id,
            'subject_id' => $item->subject_id,
            'title' => $item->title,
            'chapter_label' => $item->theme,
            'document_path' => $item->document_path,
            'document_drive_id' => $item->document_drive_id,
            'document_drive_url' => $item->document_drive_url,
            'document_name' => $item->document_name,
            'document_mime' => $item->document_mime,
            'document_size' => $item->document_size,
            'correction_document_path' => $item->correction_document_path,
            'correction_document_drive_id' => $item->correction_document_drive_id,
            'correction_document_drive_url' => $item->correction_document_drive_url,
            'correction_document_name' => $item->correction_document_name,
            'correction_document_mime' => $item->correction_document_mime,
            'correction_document_size' => $item->correction_document_size,
        ])->save();
    }
}
