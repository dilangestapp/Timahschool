<?php

namespace App\Support;

use App\Models\Course;
use App\Models\TdSet;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class DocumentBrandingService
{
    public function courseData(Course $course, ?string $publicUrl = null): array
    {
        $url = $publicUrl ?: URL::current();

        return [
            'brand_initials' => 'TA',
            'brand_name' => 'TIMAH ACADEMY',
            'slogan' => 'Pour apprendre, réviser et réussir.',
            'signature' => 'Une solution Cabrel Tech',
            'site' => 'timahacademy.online',
            'qr_url' => $this->qrUrl($url),
            'public_url' => $url,
            'title' => $course->title,
            'subtitle' => trim(($course->schoolClass->name ?? 'Classe').' · '.($course->subject->name ?? 'Matière')),
            'author' => $course->creator->full_name ?? $course->creator->name ?? $course->creator->username ?? null,
            'published_at' => $course->published_at,
            'description' => $course->description,
            'objectives' => $course->objectives,
            'content_html' => $course->content_html,
            'document_name' => $course->document_name,
            'document_mime' => $course->document_mime,
            'document_size' => method_exists($course, 'humanDocumentSize') ? $course->humanDocumentSize() : null,
            'download_name' => $this->safeName('TIMAH-ACADEMY-'.$course->title).'.html',
        ];
    }

    public function tdData(TdSet $td, bool $correction = false, ?string $publicUrl = null): array
    {
        $url = $publicUrl ?: URL::current();

        return [
            'brand_initials' => 'TA',
            'brand_name' => 'TIMAH ACADEMY',
            'slogan' => 'Pour apprendre, réviser et réussir.',
            'signature' => 'Une solution Cabrel Tech',
            'site' => 'timahacademy.online',
            'qr_url' => $this->qrUrl($url),
            'public_url' => $url,
            'title' => ($correction ? 'Corrigé - ' : '').$td->title,
            'subtitle' => trim(($td->schoolClass->name ?? 'Classe').' · '.($td->subject->name ?? 'Matière').' · '.($td->chapter_label ?: 'TD')),
            'author' => $td->author->full_name ?? $td->author->name ?? $td->author->username ?? null,
            'published_at' => $td->published_at ?? null,
            'description' => $td->description ?? null,
            'objectives' => null,
            'content_html' => $correction ? ($td->correction_html ?? '') : ($td->editable_html ?: $td->instructions_html),
            'document_name' => $correction ? $td->correction_document_name : $td->document_name,
            'document_mime' => null,
            'document_size' => null,
            'download_name' => $this->safeName('TIMAH-ACADEMY-'.($correction ? 'Corrige-' : 'TD-').$td->title).'.html',
        ];
    }

    public function qrUrl(string $data): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&margin=8&data='.rawurlencode($data);
    }

    public function safeName(string $name): string
    {
        $name = Str::ascii($name);
        $name = preg_replace('/[^A-Za-z0-9\-_]+/', '-', $name) ?: 'TIMAH-ACADEMY-document';
        return trim($name, '-_') ?: 'TIMAH-ACADEMY-document';
    }
}
