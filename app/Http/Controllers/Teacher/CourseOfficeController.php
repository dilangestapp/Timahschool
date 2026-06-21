<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\TeacherAssignment;
use App\Support\CourseDocumentExtractor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CourseOfficeController extends Controller
{
    private const EDITABLE = ['docx', 'doc', 'odt', 'rtf', 'txt'];

    public function editor(Course $course)
    {
        $this->authorizeCourse($course);

        if (!$course->document_path || !$this->isEditable($course)) {
            return redirect()->route('teacher.courses.edit', $course)
                ->with('error', 'Importez un document Word éditable : DOCX, DOC, ODT, RTF ou TXT.');
        }

        $path = storage_path('app/' . $course->document_path);
        if (!is_file($path)) {
            return redirect()->route('teacher.courses.edit', $course)
                ->with('error', 'Le document du cours est introuvable sur le serveur.');
        }

        if ($this->secret() === '') {
            return redirect()->route('teacher.courses.edit', $course)
                ->with('error', 'Le secret ONLYOFFICE n’est pas encore configuré côté Laravel.');
        }

        $config = [
            'type' => 'desktop',
            'documentType' => 'word',
            'width' => '100%',
            'height' => '100%',
            'document' => [
                'title' => $course->document_name ?: basename($course->document_path),
                'url' => route('onlyoffice.courses.file', [$course, $this->accessToken($course, 'file')]),
                'fileType' => $this->extension($course),
                'key' => $this->documentKey($course),
                'permissions' => [
                    'edit' => true,
                    'download' => true,
                    'print' => true,
                    'review' => true,
                    'comment' => true,
                ],
            ],
            'editorConfig' => [
                'mode' => 'edit',
                'lang' => 'fr',
                'callbackUrl' => route('onlyoffice.courses.callback', [$course, $this->accessToken($course, 'callback')]),
                'user' => [
                    'id' => (string) auth()->id(),
                    'name' => auth()->user()->full_name ?? auth()->user()->name ?? auth()->user()->username,
                ],
                'customization' => [
                    'autosave' => true,
                    'forcesave' => true,
                    'compactToolbar' => false,
                    'toolbarNoTabs' => false,
                    'chat' => false,
                    'help' => true,
                    'goback' => [
                        'text' => 'Retour aux cours',
                        'url' => route('teacher.courses.edit', $course),
                    ],
                ],
            ],
        ];

        $config['token'] = $this->jwt($config);

        return view('teacher.courses.office', [
            'course' => $course->load(['schoolClass', 'subject']),
            'documentServerUrl' => config('onlyoffice.document_server_url'),
            'editorConfig' => $config,
        ]);
    }

    public function convertContent(Course $course, CourseDocumentExtractor $extractor): RedirectResponse
    {
        $this->authorizeCourse($course);

        if (!$course->document_path) {
            return back()->with('error', 'Aucun fichier n’est joint à ce cours.');
        }

        $path = storage_path('app/' . $course->document_path);
        if (!is_file($path)) {
            return back()->with('error', 'Le fichier du cours est introuvable sur le serveur.');
        }

        $text = $extractor->text($course, $path);
        if (trim($text) === '') {
            return back()->with('error', 'Le contenu n’a pas pu être extrait. Pour un PDF scanné, il faudra activer l’OCR.');
        }

        $updates = [];
        if (Schema::hasColumn('courses', 'content_html')) {
            $updates['content_html'] = $extractor->html($text);
        }
        if (Schema::hasColumn('courses', 'content_text')) {
            $updates['content_text'] = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
        }

        if ($updates) {
            $course->update($updates);
        }

        return redirect()->route('teacher.courses.edit', $course)->with('success', 'Le contenu du fichier a été extrait dans l’éditeur. Relisez et corrigez la mise en forme avant publication.');
    }

    public function file(Course $course, string $token): BinaryFileResponse|JsonResponse
    {
        if (!$this->validToken($course, 'file', $token) || !$course->document_path) {
            return response()->json(['error' => 1], 403);
        }

        $path = storage_path('app/' . $course->document_path);
        if (!is_file($path)) {
            return response()->json(['error' => 1], 404);
        }

        return response()->file($path, [
            'Content-Type' => $course->document_mime ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . addslashes($course->document_name ?: basename($path)) . '"',
        ]);
    }

    public function callback(Request $request, Course $course, string $token): JsonResponse
    {
        if (!$this->validToken($course, 'callback', $token) || !$course->document_path) {
            return response()->json(['error' => 1], 403);
        }

        $payload = $request->json()->all();
        $status = (int) ($payload['status'] ?? 0);
        $url = (string) ($payload['url'] ?? '');

        if (!in_array($status, [2, 6], true)) {
            return response()->json(['error' => 0]);
        }

        if (!$this->callbackUrlIsAllowed($url)) {
            Log::warning('ONLYOFFICE callback URL refused', ['course_id' => $course->id]);
            return response()->json(['error' => 1]);
        }

        try {
            $response = Http::timeout(90)->get($url);
            if (!$response->successful()) {
                return response()->json(['error' => 1]);
            }

            Storage::disk('local')->put($course->document_path, $response->body());
            $course->forceFill([
                'document_size' => Storage::disk('local')->size($course->document_path),
            ])->save();

            return response()->json(['error' => 0]);
        } catch (\Throwable $e) {
            Log::error('ONLYOFFICE save failed', ['course_id' => $course->id, 'message' => $e->getMessage()]);
            return response()->json(['error' => 1]);
        }
    }

    private function authorizeCourse(Course $course): void
    {
        $allowed = TeacherAssignment::query()
            ->where('teacher_id', auth()->id())
            ->where('is_active', true)
            ->where('school_class_id', $course->school_class_id)
            ->where('subject_id', $course->subject_id)
            ->exists();

        abort_unless($allowed, 403);
    }

    private function isEditable(Course $course): bool
    {
        return in_array($this->extension($course), self::EDITABLE, true);
    }

    private function extension(Course $course): string
    {
        return strtolower(pathinfo($course->document_name ?: $course->document_path ?: 'document.docx', PATHINFO_EXTENSION)) ?: 'docx';
    }

    private function documentKey(Course $course): string
    {
        $path = storage_path('app/' . $course->document_path);
        return substr(hash('sha256', $course->id . '|' . $course->document_path . '|' . (is_file($path) ? filemtime($path) : time()) . '|' . $course->document_size), 0, 32);
    }

    private function accessToken(Course $course, string $purpose): string
    {
        return hash_hmac('sha256', $purpose . '|' . $course->id . '|' . $course->document_path, $this->secret());
    }

    private function validToken(Course $course, string $purpose, string $token): bool
    {
        return hash_equals($this->accessToken($course, $purpose), $token);
    }

    private function callbackUrlIsAllowed(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        $allowed = parse_url((string) config('onlyoffice.document_server_url'), PHP_URL_HOST);
        return $host && $allowed && strtolower($host) === strtolower($allowed);
    }

    private function secret(): string
    {
        return (string) config('onlyoffice.jwt_secret', '');
    }

    private function jwt(array $payload): string
    {
        $segments = [
            $this->base64Url(json_encode(['alg' => 'HS256', 'typ' => 'JWT'])),
            $this->base64Url(json_encode($payload, JSON_UNESCAPED_UNICODE)),
        ];
        $segments[] = $this->base64Url(hash_hmac('sha256', implode('.', $segments), $this->secret(), true));
        return implode('.', $segments);
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
