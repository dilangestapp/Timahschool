<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\TdAttempt;
use App\Models\TdSet;
use App\Support\DocumentBrandingService;
use Illuminate\Support\Facades\Storage;

class BrandedDocumentController extends Controller
{
    public function course(Course $course, DocumentBrandingService $branding)
    {
        $this->ensureCourseAccess($course);
        $course->loadMissing(['schoolClass', 'subject', 'creator']);

        return response()
            ->view('documents.branded.course', [
                'course' => $course,
                'brand' => $branding->courseData($course, route('documents.course.official', $course)),
                'embedUrl' => $course->hasDocument() ? route('documents.course.embed', $course) : null,
            ])
            ->header('Cache-Control', 'private, max-age=0, must-revalidate');
    }

    public function courseEmbed(Course $course)
    {
        $this->ensureCourseAccess($course);
        abort_unless($course->document_path, 404);

        $path = storage_path('app/' . $course->document_path);
        abort_unless(file_exists($path), 404);

        return response()->file($path, [
            'Content-Type' => $course->document_mime ?: 'application/octet-stream',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    public function courseDownload(Course $course, DocumentBrandingService $branding)
    {
        $this->ensureCourseAccess($course);
        $course->loadMissing(['schoolClass', 'subject', 'creator']);
        $html = view('documents.branded.course', [
            'course' => $course,
            'brand' => $branding->courseData($course, route('documents.course.official', $course)),
            'embedUrl' => $course->hasDocument() ? route('documents.course.embed', $course) : null,
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$branding->courseData($course)['download_name'].'"',
        ]);
    }

    public function td(TdSet $td, DocumentBrandingService $branding)
    {
        $this->ensureTdAccess($td, false);
        $td->loadMissing(['schoolClass', 'subject', 'author']);

        return response()
            ->view('documents.branded.td', [
                'td' => $td,
                'correction' => false,
                'brand' => $branding->tdData($td, false, route('documents.td.official', $td)),
                'embedUrl' => $td->document_path ? route('documents.td.embed', $td) : null,
            ])
            ->header('Cache-Control', 'private, max-age=0, must-revalidate');
    }

    public function tdCorrection(TdSet $td, DocumentBrandingService $branding)
    {
        $this->ensureTdAccess($td, true);
        $td->loadMissing(['schoolClass', 'subject', 'author']);

        return response()
            ->view('documents.branded.td', [
                'td' => $td,
                'correction' => true,
                'brand' => $branding->tdData($td, true, route('documents.td.correction.official', $td)),
                'embedUrl' => $td->correction_document_path ? route('documents.td.correction.embed', $td) : null,
            ])
            ->header('Cache-Control', 'private, max-age=0, must-revalidate');
    }

    public function tdEmbed(TdSet $td)
    {
        $this->ensureTdAccess($td, false);
        abort_unless($td->document_path && Storage::disk('public')->exists($td->document_path), 404);
        return Storage::disk('public')->response($td->document_path, $td->document_name ?: $td->slug);
    }

    public function tdCorrectionEmbed(TdSet $td)
    {
        $this->ensureTdAccess($td, true);
        abort_unless($td->correction_document_path && Storage::disk('public')->exists($td->correction_document_path), 404);
        return Storage::disk('public')->response($td->correction_document_path, $td->correction_document_name ?: $td->slug);
    }

    protected function ensureCourseAccess(Course $course): void
    {
        abort_unless($course->status === Course::STATUS_PUBLISHED, 404);

        if (auth()->check()) {
            $user = auth()->user();

            if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                return;
            }

            if (method_exists($user, 'isTeacher') && $user->isTeacher() && (int) $course->created_by === (int) $user->id) {
                return;
            }

            $profile = $user->studentProfile ?? null;
            if ($profile && (int) $profile->school_class_id === (int) $course->school_class_id) {
                return;
            }

            if (method_exists($user, 'isParent') && $user->isParent()) {
                return;
            }
        }

        abort(403);
    }

    protected function ensureTdAccess(TdSet $td, bool $correction): void
    {
        abort_unless($td->status === TdSet::STATUS_PUBLISHED || (auth()->user()?->isTeacher() && (int) $td->author_user_id === (int) auth()->id()), 404);

        if (!auth()->check()) {
            abort(403);
        }

        $user = auth()->user();
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return;
        }

        if (method_exists($user, 'isTeacher') && $user->isTeacher() && (int) $td->author_user_id === (int) $user->id) {
            return;
        }

        $profile = $user->studentProfile ?? null;
        if ($profile && (int) $td->school_class_id === (int) $profile->school_class_id) {
            if (!$correction) {
                return;
            }

            $attempt = TdAttempt::query()->where('td_set_id', $td->id)->where('student_id', $user->id)->latest('id')->first();
            abort_unless($td->correctionIsAvailableFor($user, $attempt), 403);
            return;
        }

        abort(403);
    }
}
