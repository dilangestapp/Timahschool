<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PedagogicalBankItem;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TdSet;
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
            ->with(['schoolClass', 'subject'])
            ->when($classId, fn ($query) => $query->where('school_class_id', $classId))
            ->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId))
            ->when($type !== 'all', fn ($query) => $query->where('content_type', $type))
            ->latest('id');

        $availableItems = (clone $base)->where('status', PedagogicalBankItem::STATUS_AVAILABLE)->take(80)->get();
        $usedItems = (clone $base)->where('status', PedagogicalBankItem::STATUS_USED)->take(80)->get();
        $archivedItems = (clone $base)->where('status', PedagogicalBankItem::STATUS_ARCHIVED)->take(80)->get();

        if ($status === 'used') {
            $availableItems = collect();
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_class_id' => ['nullable', 'integer'],
            'subject_id' => ['nullable', 'integer'],
            'content_type' => ['required', 'string', 'max:40'],
            'title' => ['nullable', 'string', 'max:255'],
            'document' => ['required', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg', 'max:20480'],
            'correction_document' => ['nullable', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg', 'max:20480'],
        ]);

        $document = $request->file('document');
        $correction = $request->file('correction_document');
        $inferred = $this->inferFromFileName($document->getClientOriginalName());

        $payload = [
            'school_class_id' => $data['school_class_id'] ?: $this->resolveClassId($inferred['class_label']),
            'subject_id' => $data['subject_id'] ?: $this->resolveSubjectId($inferred['subject_label']),
            'created_by' => auth()->id(),
            'code' => $inferred['code'],
            'title' => $data['title'] ?: $inferred['title'],
            'content_type' => $data['content_type'],
            'inferred_class' => $inferred['class_label'],
            'inferred_subject' => $inferred['subject_label'],
            'theme' => $inferred['theme'],
            'status' => PedagogicalBankItem::STATUS_AVAILABLE,
        ];

        $payload = array_merge($payload, $this->storeUploadedFile($document, 'document'));

        if ($correction) {
            $payload = array_merge($payload, $this->storeUploadedFile($correction, 'correction_document'));
        }

        PedagogicalBankItem::query()->create($payload);

        return back()->with('success', 'Document ajouté à la banque pédagogique.');
    }

    public function schedule(Request $request, PedagogicalBankItem $item)
    {
        if ($item->content_type !== PedagogicalBankItem::TYPE_TD) {
            return back()->with('error', 'Pour la V1 test, la programmation automatique concerne d’abord les TD PDF.');
        }

        $data = $request->validate([
            'publish_at' => ['nullable', 'date'],
            'correction_delay_minutes' => ['required', 'integer', 'min:0', 'max:1440'],
            'access_level' => ['required', 'string', 'max:40'],
        ]);

        if (!$item->school_class_id || !$item->subject_id) {
            return back()->with('error', 'Classe ou matière manquante. Corrigez la fiche dans la banque avant programmation.');
        }

        $td = DB::transaction(function () use ($item, $data) {
            $td = TdSet::query()->create([
                'pedagogical_bank_item_id' => $item->id,
                'school_class_id' => $item->school_class_id,
                'subject_id' => $item->subject_id,
                'author_user_id' => auth()->id(),
                'title' => $item->title,
                'slug' => Str::slug($item->title . '-' . $item->id . '-' . now()->timestamp),
                'chapter_label' => $item->theme,
                'difficulty' => 'standard',
                'access_level' => $data['access_level'],
                'correction_delay_minutes' => (int) $data['correction_delay_minutes'],
                'status' => TdSet::STATUS_PUBLISHED,
                'document_path' => $item->document_path,
                'document_name' => $item->document_name,
                'document_mime' => $item->document_mime,
                'document_size' => $item->document_size,
                'correction_document_path' => $item->correction_document_path,
                'correction_document_name' => $item->correction_document_name,
                'correction_document_mime' => $item->correction_document_mime,
                'correction_document_size' => $item->correction_document_size,
                'published_at' => $data['publish_at'] ? now()->parse($data['publish_at']) : now(),
            ]);

            $item->update([
                'status' => PedagogicalBankItem::STATUS_USED,
                'times_used' => ((int) $item->times_used) + 1,
                'last_scheduled_at' => now(),
                'last_td_set_id' => $td->id,
            ]);

            return $td;
        });

        return back()->with('success', 'TD programmé et publié. Il est maintenant dans les TD déjà utilisés.');
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

    private function storeUploadedFile($file, string $prefix): array
    {
        $path = $file->store('pedagogical-bank', 'public');

        return [
            $prefix . '_path' => $path,
            $prefix . '_name' => $file->getClientOriginalName(),
            $prefix . '_mime' => $file->getClientMimeType(),
            $prefix . '_size' => $file->getSize(),
        ];
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

        return SchoolClass::query()->where('name', $label)->value('id')
            ?: SchoolClass::query()->where('name', 'like', '%' . $label . '%')->value('id');
    }

    private function resolveSubjectId(?string $label): ?int
    {
        if (!$label) {
            return null;
        }

        return Subject::query()->where('name', $label)->value('id')
            ?: Subject::query()->where('name', 'like', '%' . $label . '%')->value('id');
    }
}
