<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TeacherAssignment;
use App\Models\TeacherMessage;
use App\Services\AnonymousVoiceTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $student = auth()->user();
        $studentProfile = $student->studentProfile;

        abort_unless($studentProfile && $studentProfile->school_class_id, 403, 'Aucune classe élève trouvée.');

        $assignments = Schema::hasTable('teacher_assignments')
            ? TeacherAssignment::query()
                ->with(['teacher', 'subject', 'schoolClass'])
                ->where('school_class_id', $studentProfile->school_class_id)
                ->where('is_active', true)
                ->get()
            : collect();

        $messages = $this->messagesForStudent($student->id, $studentProfile->school_class_id);
        $assignmentMap = $assignments->keyBy('id');

        $messages->pluck('assignment')
            ->filter()
            ->each(function ($assignment) use ($assignmentMap, $studentProfile) {
                if ((int) $assignment->school_class_id === (int) $studentProfile->school_class_id && !$assignmentMap->has($assignment->id)) {
                    $assignmentMap->put($assignment->id, $assignment);
                }
            });

        $threads = $this->buildThreads($assignmentMap->values(), $messages);

        $requestedThreadId = (int) $request->query('thread');
        $selectedThreadId = $threads->contains(fn ($thread) => (int) $thread->assignment->id === $requestedThreadId)
            ? $requestedThreadId
            : optional($threads->first())->assignment->id;

        if ($selectedThreadId && Schema::hasTable('teacher_messages')) {
            TeacherMessage::query()
                ->where('student_id', $student->id)
                ->where('teacher_assignment_id', $selectedThreadId)
                ->where('school_class_id', $studentProfile->school_class_id)
                ->where(function ($query) {
                    $query->where('direction', TeacherMessage::DIRECTION_TEACHER)
                        ->orWhereNull('direction');
                })
                ->whereNull('read_at')
                ->update(['status' => TeacherMessage::STATUS_READ, 'read_at' => now()]);

            $messages = $this->messagesForStudent($student->id, $studentProfile->school_class_id);
            $threads = $this->buildThreads($assignmentMap->values(), $messages);
        }

        return view('student.messages.index', [
            'threads' => $threads,
            'selectedThreadId' => $selectedThreadId,
            'studentProfile' => $studentProfile,
        ]);
    }

    public function create(Request $request)
    {
        $student = auth()->user();
        $studentProfile = $student->studentProfile;

        abort_unless($studentProfile && $studentProfile->school_class_id, 403, 'Aucune classe élève trouvée.');

        $assignments = Schema::hasTable('teacher_assignments')
            ? TeacherAssignment::query()
                ->with(['teacher', 'subject', 'schoolClass'])
                ->where('school_class_id', $studentProfile->school_class_id)
                ->where('is_active', true)
                ->get()
            : collect();

        $selectedAssignmentId = (int) $request->query('teacher_assignment_id');
        $selectedAssignment = $assignments->firstWhere('id', $selectedAssignmentId);

        return view('student.messages.create', compact('assignments', 'studentProfile', 'selectedAssignmentId', 'selectedAssignment'));
    }

    public function store(Request $request, AnonymousVoiceTransformer $voiceTransformer)
    {
        $student = auth()->user();
        $studentProfile = $student->studentProfile;

        abort_unless($studentProfile && $studentProfile->school_class_id, 403, 'Aucune classe élève trouvée.');

        $data = $request->validate([
            'teacher_assignment_id' => ['required', 'integer', 'exists:teacher_assignments,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'parent_message_id' => ['nullable', 'integer'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,jpg,jpeg,png,webp,gif,mp3,wav,ogg,m4a,webm,aac,3gp,amr,mp4', 'max:20480'],
            'voice_note' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a,webm,aac,3gp,amr,mp4', 'max:20480'],
        ]);

        $assignment = TeacherAssignment::query()
            ->where('id', $data['teacher_assignment_id'])
            ->where('school_class_id', $studentProfile->school_class_id)
            ->where('is_active', true)
            ->firstOrFail();

        $cleanMessage = trim((string) ($data['message'] ?? ''));

        if (!$request->file('attachment') && !$request->file('voice_note') && $cleanMessage === '') {
            return back()
                ->withErrors(['message' => 'Écrivez un message ou ajoutez un fichier / vocal.'])
                ->withInput();
        }

        [$attachmentPath, $attachmentName, $attachmentMime, $attachmentSize] = [null, null, null, null];

        if ($request->file('voice_note')) {
            $voiceData = $voiceTransformer->store($request->file('voice_note'));
            $attachmentPath = $voiceData['path'];
            $attachmentName = $voiceData['name'];
            $attachmentMime = $request->file('voice_note')->getMimeType();
            $attachmentSize = $request->file('voice_note')->getSize();
        } elseif ($request->file('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('teacher_messages', 'local');
            $attachmentName = $file->getClientOriginalName();
            $attachmentMime = $file->getMimeType();
            $attachmentSize = $file->getSize();
        }

        $payload = [
            'teacher_assignment_id' => $assignment->id,
            'teacher_id' => $assignment->teacher_id,
            'student_id' => $student->id,
            'school_class_id' => $assignment->school_class_id,
            'subject_id' => $assignment->subject_id,
            'title' => $data['title'] ?: 'Conversation',
            'message' => $cleanMessage !== '' ? $cleanMessage : ($attachmentName ?: 'Note vocale'),
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'status' => TeacherMessage::STATUS_UNREAD,
        ];

        if (Schema::hasColumn('teacher_messages', 'direction')) {
            $payload['direction'] = TeacherMessage::DIRECTION_STUDENT;
        }
        if (Schema::hasColumn('teacher_messages', 'parent_message_id')) {
            $payload['parent_message_id'] = $data['parent_message_id'] ?? null;
        }
        if (Schema::hasColumn('teacher_messages', 'attachment_mime')) {
            $payload['attachment_mime'] = $attachmentMime;
        }
        if (Schema::hasColumn('teacher_messages', 'attachment_size')) {
            $payload['attachment_size'] = $attachmentSize;
        }
        if (Schema::hasColumn('teacher_messages', 'topic')) {
            $payload['topic'] = $payload['title'];
        }
        if (Schema::hasColumn('teacher_messages', 'delivered_at')) {
            $payload['delivered_at'] = now();
        }

        TeacherMessage::query()->create($payload);

        return redirect()
            ->route('student.messages.index', ['thread' => $assignment->id])
            ->with('success', 'Message envoyé.');
    }

    public function attachment(Request $request, TeacherMessage $message)
    {
        abort_unless((int) $message->student_id === (int) auth()->id(), 403);
        abort_unless($message->attachment_path && Storage::disk('local')->exists($message->attachment_path), 404);

        $absolutePath = Storage::disk('local')->path($message->attachment_path);
        $downloadName = $message->attachment_name ?? basename($message->attachment_path);

        if ($request->boolean('download')) {
            return response()->download($absolutePath, $downloadName);
        }

        if ($message->isImageAttachment() || $message->isAudioAttachment()) {
            return response()->file($absolutePath);
        }

        return response()->download($absolutePath, $downloadName);
    }

    private function messagesForStudent(int $studentId, int $classId)
    {
        return Schema::hasTable('teacher_messages')
            ? TeacherMessage::query()
                ->with(['teacher', 'subject', 'schoolClass', 'assignment.teacher', 'assignment.subject', 'assignment.schoolClass', 'parentMessage'])
                ->where('student_id', $studentId)
                ->where('school_class_id', $classId)
                ->when(Schema::hasColumn('teacher_messages', 'deleted_by_student_at'), fn ($query) => $query->whereNull('deleted_by_student_at'))
                ->orderBy('created_at')
                ->get()
            : collect();
    }

    private function buildThreads($assignments, $messages)
    {
        return $assignments
            ->map(function ($assignment) use ($messages) {
                $conversation = $messages
                    ->where('teacher_assignment_id', $assignment->id)
                    ->sortBy('created_at')
                    ->values();

                $latest = $conversation->last();
                $unreadCount = $conversation
                    ->filter(function ($message) {
                        $direction = (string) ($message->direction ?? TeacherMessage::DIRECTION_TEACHER);
                        return $direction === TeacherMessage::DIRECTION_TEACHER && !$message->read_at;
                    })
                    ->count();

                return (object) [
                    'assignment' => $assignment,
                    'teacher' => $assignment->teacher,
                    'subject' => $assignment->subject,
                    'schoolClass' => $assignment->schoolClass,
                    'messages' => $conversation,
                    'latest_message' => $latest,
                    'has_messages' => $conversation->isNotEmpty(),
                    'unread_count' => $unreadCount,
                    'attachment_count' => $conversation->filter(fn ($message) => !empty($message->attachment_path))->count(),
                    'sort_timestamp' => $latest && $latest->created_at ? $latest->created_at->timestamp : 0,
                ];
            })
            ->sort(function ($a, $b) {
                if ($a->sort_timestamp === $b->sort_timestamp) {
                    $aName = strtolower((string) ($a->teacher->full_name ?? $a->teacher->name ?? $a->teacher->username ?? ''));
                    $bName = strtolower((string) ($b->teacher->full_name ?? $b->teacher->name ?? $b->teacher->username ?? ''));
                    return $aName <=> $bName;
                }
                return $b->sort_timestamp <=> $a->sort_timestamp;
            })
            ->values();
    }
}
