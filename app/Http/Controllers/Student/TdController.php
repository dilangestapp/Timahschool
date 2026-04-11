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
        $profile = $user->studentProfile;
        abort_unless($profile && (int) $td->school_class_id === (int) $profile->school_class_id, 403);
        abort_unless($td->status === TdSet::STATUS_PUBLISHED, 404);
        abort_unless($td->canStudentAccess($user), 403, 'Ce TD est réservé aux abonnés.');

        $attempt = TdAttempt::query()->firstOrCreate(
            ['td_set_id' => $td->id, 'student_id' => $user->id],
            ['status' => TdAttempt::STATUS_OPENED, 'opened_at' => now()]
        );

        $thread = TdQuestionThread::query()
            ->with(['messages.sender'])
            ->where('td_set_id', $td->id)
            ->where('student_id', $user->id)
            ->first();

        return view('student.td.show', [
            'td' => $td->load(['subject', 'schoolClass', 'author']),
            'attempt' => $attempt,
            'thread' => $thread,
            'subscription' => $user->activeSubscription,
            'canSeeCorrection' => $td->canStudentAccess($user),
        ]);
    }

    public function complete(TdSet $td)
    {
        $user = auth()->user();
        $profile = $user->studentProfile;
        abort_unless($profile && (int) $td->school_class_id === (int) $profile->school_class_id, 403);
        abort_unless($td->canStudentAccess($user), 403);

        TdAttempt::query()->updateOrCreate(
            ['td_set_id' => $td->id, 'student_id' => $user->id],
            [
                'status' => TdAttempt::STATUS_COMPLETED,
                'opened_at' => now(),
                'completed_at' => now(),
                'submitted_at' => now(),
                'correction_unlocked_at' => now(),
            ]
        );

        return back()->with('success', 'TD marqué comme terminé.');
    }

    public function ask(Request $request, TdSet $td)
    {
        $user = auth()->user();
        $profile = $user->studentProfile;
        abort_unless($profile && (int) $td->school_class_id === (int) $profile->school_class_id, 403);
        abort_unless($td->canStudentAccess($user), 403);

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
        $profile = $user->studentProfile;
        abort_unless($profile && (int) $td->school_class_id === (int) $profile->school_class_id, 403);
        abort_unless($td->canStudentAccess($user), 403);
        abort_unless($td->document_path, 404);

        return Storage::disk('public')->response($td->document_path, $td->document_name ?: basename($td->document_path));
    }

    public function correctionDocument(TdSet $td)
    {
        $user = auth()->user();
        $profile = $user->studentProfile;
        abort_unless($profile && (int) $td->school_class_id === (int) $profile->school_class_id, 403);
        abort_unless($td->canStudentAccess($user), 403);
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
}
