<?php

namespace App\Http\Controllers;

use App\Models\TdAttempt;
use App\Models\TdSet;
use App\Support\SimplePdfDocument;
use Illuminate\Support\Facades\Storage;

class TdPdfDocumentController extends Controller
{
    public function adminDocument(TdSet $td)
    {
        return $this->documentResponse($td, false, true);
    }

    public function adminCorrection(TdSet $td)
    {
        return $this->documentResponse($td, true, true);
    }

    public function studentDocument(TdSet $td)
    {
        $this->ensureStudentAccess($td);
        return $this->documentResponse($td, false, false);
    }

    public function studentCorrection(TdSet $td)
    {
        $this->ensureStudentAccess($td);

        $attempt = TdAttempt::query()
            ->where('td_set_id', $td->id)
            ->where('student_id', auth()->id())
            ->latest('id')
            ->first();

        if (!$td->correctionIsAvailableFor(auth()->user(), $attempt)) {
            return back()->with('info', 'Le corrigé de ce TD sera disponible après la fin du temps de travail défini.');
        }

        return $this->documentResponse($td, true, false);
    }

    private function ensureStudentAccess(TdSet $td): void
    {
        $user = auth()->user();
        $profile = $user?->studentProfile;

        abort_unless($profile, 403);
        abort_unless((int) $td->school_class_id === (int) $profile->school_class_id, 403);
        abort_unless($td->status === TdSet::STATUS_PUBLISHED, 404);
        abort_unless($td->canStudentAccess($user), 403);
    }

    private function documentResponse(TdSet $td, bool $correction, bool $admin)
    {
        $path = $correction ? $td->correction_document_path : $td->document_path;
        $name = $correction ? $td->correction_document_name : $td->document_name;

        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path, $this->pdfName($name ?: $td->slug, $correction));
        }

        $html = $correction ? (string) $td->correction_html : (string) ($td->editable_html ?: $td->instructions_html);
        abort_if(trim($html) === '', 404);

        $title = ($correction ? 'Corrigé - ' : '').$td->title;
        $subtitle = trim(($td->schoolClass->name ?? 'Classe').' · '.($td->subject->name ?? 'Matière').' · '.($td->chapter_label ?: 'TD'));
        $footer = ($correction ? 'Corrigé' : 'TD').' · '.($td->source_label ?: $td->slug);

        $pdf = SimplePdfDocument::make($title, $subtitle, $html, $footer);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$this->pdfName($td->slug ?: 'td', $correction).'"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ]);
    }

    private function pdfName(string $name, bool $correction): string
    {
        $clean = trim(strtolower($name));
        $clean = preg_replace('/\.pdf$/i', '', $clean) ?: 'td';
        $clean = preg_replace('/[^a-z0-9\-_]+/i', '-', $clean);
        $clean = trim($clean, '-_') ?: 'td';

        return ($correction ? 'corrige-' : '').$clean.'.pdf';
    }
}
