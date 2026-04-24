<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TdAttempt;
use App\Models\TdQuestionMessage;
use App\Models\TdQuestionThread;
use App\Models\TdSet;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class TdController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $profile = $user->studentProfile;
        abort_unless($profile, 403, 'Profil élève introuvable.');

        $query = TdSet::query()
            ->with(['subject', 'schoolClass'])
            ->published()
            ->where('school_class_id', $profile->school_class_id);

        if ($request->filled('subject_id')) {
            $query->where('subject_id', (int) $request->subject_id);
        }

        if ($request->filled('access_level')) {
            $query->where('access_level', (string) $request->string('access_level'));
        }

        $sets = $query->latest('published_at')->paginate(12)->withQueryString();

        return view('student.td.index', [
            'sets' => $sets,
            'subjects' => TeacherAssignment::query()->with('subject')
                ->where('school_class_id', $profile->school_class_id)
                ->where('is_active', true)
                ->get()
                ->pluck('subject')
                ->filter()
                ->unique('id')
                ->sortBy('name')
                ->values(),
            'filters' => $request->only('subject_id', 'access_level'),
            'subscription' => $user->activeSubscription,
        ]);
    }

    public function show(TdSet $td)
    {
        $user = auth()->user();
        if ($response = $this->ensureStudentCanAccessTd($td, $user, true)) {
            return $response;
        }

        $delayMinutes = $td->correctionDelayMinutes();

        $attempt = TdAttempt::query()->firstOrCreate(
            ['td_set_id' => $td->id, 'student_id' => $user->id],
            $this->buildAttemptAttributes([
                'status' => TdAttempt::STATUS_OPENED,
                'opened_at' => now(),
                'correction_unlocked_at' => now()->addMinutes($delayMinutes),
            ])
        );

        $updates = [];

        if (!$attempt->opened_at) {
            $updates['opened_at'] = now();
        }

        if (!$attempt->correction_unlocked_at) {
            $updates['correction_unlocked_at'] = ($attempt->opened_at ?: now())->copy()->addMinutes($delayMinutes);
        }

        if ($attempt->status !== TdAttempt::STATUS_COMPLETED) {
            $updates['status'] = TdAttempt::STATUS_OPENED;
        }

        if (!empty($updates)) {
            $attempt->update($this->buildAttemptAttributes($updates));
            $attempt->refresh();
        }

        $thread = TdQuestionThread::query()
            ->with(['messages.sender'])
            ->where('td_set_id', $td->id)
            ->where('student_id', $user->id)
            ->first();

        $secondsRemaining = $attempt->correction_unlocked_at
            ? max(0, now()->diffInSeconds($attempt->correction_unlocked_at, false))
            : 0;

        return view('student.td.show', [
            'td' => $td->load(['subject', 'schoolClass', 'author']),
            'attempt' => $attempt,
            'thread' => $thread,
            'subscription' => $user->activeSubscription,
            'canSeeCorrection' => $td->correctionIsAvailableFor($user, $attempt),
            'correctionDelayMinutes' => $delayMinutes,
            'correctionUnlockAt' => $attempt->correction_unlocked_at,
            'correctionSecondsRemaining' => $secondsRemaining,
        ]);
    }

    public function complete(TdSet $td)
    {
        $user = auth()->user();
        if ($response = $this->ensureStudentCanAccessTd($td, $user)) {
            return $response;
        }

        $delayMinutes = $td->correctionDelayMinutes();

        $attempt = TdAttempt::query()->firstOrCreate(
            ['td_set_id' => $td->id, 'student_id' => $user->id],
            $this->buildAttemptAttributes([
                'status' => TdAttempt::STATUS_OPENED,
                'opened_at' => now(),
                'correction_unlocked_at' => now()->addMinutes($delayMinutes),
            ])
        );

        $unlockAt = $attempt->correction_unlocked_at ?: ($attempt->opened_at ?: now())->copy()->addMinutes($delayMinutes);

        $attempt->update($this->buildAttemptAttributes([
            'status' => TdAttempt::STATUS_COMPLETED,
            'opened_at' => $attempt->opened_at ?: now(),
            'completed_at' => now(),
            'submitted_at' => now(),
            'correction_unlocked_at' => $unlockAt,
        ]));

        $remaining = max(0, now()->diffInMinutes($unlockAt, false));

        if ($remaining > 0) {
            return back()->with('success', 'TD marqué comme terminé. Le corrigé restera verrouillé jusqu’à la fin du temps de traitement défini par l’enseignant.');
        }

        return back()->with('success', 'TD marqué comme terminé. Le corrigé est maintenant disponible.');
    }

    public function ask(Request $request, TdSet $td)
    {
        $user = auth()->user();
        if ($response = $this->ensureStudentCanAccessTd($td, $user)) {
            return $response;
        }

        $data = $request->validate([
            'message_html' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,txt'],
        ]);

        $teacherId = optional($td->assignment)->teacher_id;
        abort_unless($teacherId, 422, 'Aucun enseignant référent n’est affecté à ce TD.');

        $thread = TdQuestionThread::query()->firstOrCreate(
            [
                'td_set_id' => $td->id,
                'student_id' => $user->id,
            ],
            [
                'school_class_id' => $td->school_class_id,
                'subject_id' => $td->subject_id,
                'teacher_id' => $teacherId,
                'status' => TdQuestionThread::STATUS_OPEN,
                'last_message_at' => now(),
            ]
        );

        $attachmentPath = null;
        $attachmentName = null;
        $attachmentMime = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('td/question_attachments', 'public');
            $attachmentName = $file->getClientOriginalName();
            $attachmentMime = $file->getMimeType();
        }

        TdQuestionMessage::query()->create([
            'thread_id' => $thread->id,
            'sender_id' => $user->id,
            'sender_role' => 'student',
            'message_html' => $data['message_html'],
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_mime' => $attachmentMime,
            'is_read' => false,
        ]);

        $thread->update([
            'status' => TdQuestionThread::STATUS_OPEN,
            'last_message_at' => now(),
        ]);

        return back()->with('success', 'Votre question a été envoyée.');
    }

    public function document(TdSet $td)
    {
        $user = auth()->user();
        if ($response = $this->ensureStudentCanAccessTd($td, $user)) {
            return $response;
        }
        abort_unless($td->document_path, 404);

        return Storage::disk('public')->response($td->document_path, $td->document_name ?: basename($td->document_path));
    }

    public function correctionDocument(TdSet $td)
    {
        $user = auth()->user();
        if ($response = $this->ensureStudentCanAccessTd($td, $user)) {
            return $response;
        }

        $attempt = TdAttempt::query()
            ->where('td_set_id', $td->id)
            ->where('student_id', $user->id)
            ->latest('id')
            ->first();

        if (!$td->correctionIsAvailableFor($user, $attempt)) {
            return back()->with('info', 'Le corrigé de ce TD sera disponible après la fin du temps de traitement défini par l’enseignant et après validation du TD.');
        }

        abort_unless($td->correction_document_path, 404);

        return Storage::disk('public')->response($td->correction_document_path, $td->correction_document_name ?: basename($td->correction_document_path));
    }

    public function attachment(TdQuestionMessage $message)
    {
        $thread = $message->thread;
        abort_unless($thread && (int) $thread->student_id === (int) auth()->id(), 403);
        abort_unless($message->attachment_path, 404);

        return Storage::disk('public')->response($message->attachment_path, $message->attachment_name ?: basename($message->attachment_path));
    }

    protected function buildAttemptAttributes(array $attributes): array
    {
        static $columns = null;

        if ($columns === null) {
            $columns = Schema::hasTable('td_attempts')
                ? collect(Schema::getColumnListing('td_attempts'))->flip()->all()
                : [];
        }

        return collect($attributes)
            ->filter(fn ($value, $key) => array_key_exists($key, $columns))
            ->all();
    }

    protected function ensureStudentCanAccessTd(TdSet $td, $user, bool $strictPublished = false): ?Response
    {
        $profile = optional($user)->studentProfile;

        if (!$profile) {
            if ($user && method_exists($user, 'isTeacher') && $user->isTeacher()) {
                return redirect()->route('teacher.dashboard')
                    ->with('warning', 'Vous êtes connecté en profil enseignant. Redirection vers votre espace enseignant.');
            }

            return redirect()->route('student.dashboard')
                ->with('error', 'Profil élève introuvable pour accéder à ce TD.');
        }

        if ((int) $td->school_class_id !== (int) $profile->school_class_id) {
            return redirect()->route('student.td.index')
                ->with('warning', 'Ce TD n’appartient pas à votre classe.');
        }

        if ($strictPublished && $td->status !== TdSet::STATUS_PUBLISHED) {
            abort(404);
        }

        if (!$td->canStudentAccess($user)) {
            return redirect()->route('student.subscription.required')
                ->with('info', 'Ce TD est réservé aux abonnés actifs.');
        }

        return null;
    }
}
