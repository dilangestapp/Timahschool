<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherAssignment;
use App\Models\TeacherMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $assignments = $this->assignments();
        $students = $this->studentsForAssignments($assignments);
        $messages = $this->messagesForAssignments($assignments);

        $threads = $students->map(function (User $student) use ($messages, $assignments) {
            $studentMessages = $messages->where('student_id', $student->id)->sortBy('created_at')->values();
            $latest = $studentMessages->last();
            $assignment = $assignments->firstWhere('school_class_id', optional($student->studentProfile)->school_class_id) ?: $assignments->first();
            $unread = $studentMessages
                ->where('direction', TeacherMessage::DIRECTION_STUDENT)
                ->where('status', TeacherMessage::STATUS_UNREAD)
                ->count();

            return (object) [
                'student' => $student,
                'assignment' => $assignment,
                'messages' => $studentMessages,
                'latest_message' => $latest,
                'unread_count' => $unread,
                'attachment_count' => $studentMessages->filter(fn ($message) => !empty($message->attachment_path))->count(),
                'sort_timestamp' => $latest?->created_at?->timestamp ?? 0,
            ];
        })->sort(function ($a, $b) {
            if ($a->sort_timestamp === $b->sort_timestamp) {
                return strtolower($a->student->full_name ?? $a->student->name ?? $a->student->username ?? '') <=> strtolower($b->student->full_name ?? $b->student->name ?? $b->student->username ?? '');
            }
            return $b->sort_timestamp <=> $a->sort_timestamp;
        })->values();

        $selectedStudentId = (int) $request->query('student');
        if (!$selectedStudentId && $request->query('thread')) {
            $parts = explode('-', (string) $request->query('thread'));
            $selectedStudentId = (int) end($parts);
        }
        if (!$threads->contains(fn ($thread) => (int) $thread->student->id === $selectedStudentId)) {
            $selectedStudentId = (int) optional($threads->first())->student->id;
        }

        if ($selectedStudentId) {
            TeacherMessage::query()
                ->where('teacher_id', auth()->id())
                ->where('student_id', $selectedStudentId)
                ->where('direction', TeacherMessage::DIRECTION_STUDENT)
                ->where('status', TeacherMessage::STATUS_UNREAD)
                ->update(['status' => TeacherMessage::STATUS_READ, 'read_at' => now()]);

            $messages = $this->messagesForAssignments($assignments);
            $threads = $threads->map(function ($thread) use ($messages) {
                $studentMessages = $messages->where('student_id', $thread->student->id)->sortBy('created_at')->values();
                $thread->messages = $studentMessages;
                $thread->latest_message = $studentMessages->last();
                $thread->unread_count = $studentMessages
                    ->where('direction', TeacherMessage::DIRECTION_STUDENT)
                    ->where('status', TeacherMessage::STATUS_UNREAD)
                    ->count();
                return $thread;
            });
        }

        $selectedThread = $threads->first(fn ($thread) => (int) $thread->student->id === $selectedStudentId);

        return view('teacher.messages.index', [
            'threads' => $threads,
            'selectedStudentId' => $selectedStudentId,
            'selectedThread' => $selectedThread,
            'assignments' => $assignments,
        ]);
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer'],
            'teacher_assignment_id' => ['nullable', 'integer'],
            'message' => ['nullable', 'string'],
            'parent_message_id' => ['nullable', 'integer'],
            'attachment' => ['nullable', 'file', 'max:20480', 'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,mp3,wav,ogg,m4a,webm,aac,3gp,amr,mp4'],
        ]);

        $assignments = $this->assignments();
        $student = $this->authorizedStudent((int) $data['student_id'], $assignments);
        $assignment = $this->assignmentForStudent($student, $assignments, (int) ($data['teacher_assignment_id'] ?? 0));

        $cleanMessage = trim((string) ($data['message'] ?? ''));
        if (!$request->hasFile('attachment') && $cleanMessage === '') {
            return back()->withErrors(['message' => 'Écrivez un message ou ajoutez un fichier.']);
        }

        [$path, $name, $mime, $size] = $this->storeAttachment($request);

        $payload = [
            'teacher_assignment_id' => $assignment?->id,
            'teacher_id' => auth()->id(),
            'student_id' => $student->id,
            'school_class_id' => $student->studentProfile->school_class_id,
            'subject_id' => $assignment?->subject_id,
            'title' => 'Conversation',
            'message' => $cleanMessage !== '' ? $cleanMessage : ($name ?: 'Pièce jointe'),
            'attachment_path' => $path,
            'attachment_name' => $name,
            'status' => TeacherMessage::STATUS_SENT,
        ];

        if (Schema::hasColumn('teacher_messages', 'direction')) $payload['direction'] = TeacherMessage::DIRECTION_TEACHER;
        if (Schema::hasColumn('teacher_messages', 'parent_message_id')) $payload['parent_message_id'] = $data['parent_message_id'] ?? null;
        if (Schema::hasColumn('teacher_messages', 'attachment_mime')) $payload['attachment_mime'] = $mime;
        if (Schema::hasColumn('teacher_messages', 'attachment_size')) $payload['attachment_size'] = $size;
        if (Schema::hasColumn('teacher_messages', 'delivered_at')) $payload['delivered_at'] = now();
        if (Schema::hasColumn('teacher_messages', 'topic')) $payload['topic'] = 'Conversation';

        TeacherMessage::query()->create($payload);

        return redirect()->route('teacher.messages.index', ['student' => $student->id])->with('success', 'Message envoyé.');
    }

    public function broadcast(Request $request)
    {
        $data = $request->validate([
            'school_class_id' => ['required', 'integer'],
            'teacher_assignment_id' => ['nullable', 'integer'],
            'message' => ['required', 'string'],
        ]);

        $assignments = $this->assignments();
        $assignment = $assignments->firstWhere('id', (int) ($data['teacher_assignment_id'] ?? 0))
            ?: $assignments->firstWhere('school_class_id', (int) $data['school_class_id']);
        abort_unless($assignment, 403);

        $students = $this->studentsForAssignments($assignments)
            ->filter(fn ($student) => (int) optional($student->studentProfile)->school_class_id === (int) $data['school_class_id']);

        foreach ($students as $student) {
            $payload = [
                'teacher_assignment_id' => $assignment->id,
                'teacher_id' => auth()->id(),
                'student_id' => $student->id,
                'school_class_id' => $assignment->school_class_id,
                'subject_id' => $assignment->subject_id,
                'title' => 'Message de classe',
                'message' => trim($data['message']),
                'status' => TeacherMessage::STATUS_SENT,
            ];
            if (Schema::hasColumn('teacher_messages', 'direction')) $payload['direction'] = TeacherMessage::DIRECTION_TEACHER;
            if (Schema::hasColumn('teacher_messages', 'topic')) $payload['topic'] = 'Message de classe';
            if (Schema::hasColumn('teacher_messages', 'delivered_at')) $payload['delivered_at'] = now();
            TeacherMessage::query()->create($payload);
        }

        return redirect()->route('teacher.messages.index')->with('success', 'Message envoyé à la classe.');
    }

    public function show(TeacherMessage $message)
    {
        $this->authorizeMessage($message);
        if ($message->status === TeacherMessage::STATUS_UNREAD) {
            $message->update(['status' => TeacherMessage::STATUS_READ, 'read_at' => now()]);
        }
        return redirect()->route('teacher.messages.index', ['student' => $message->student_id]);
    }

    public function reply(Request $request, TeacherMessage $message)
    {
        $this->authorizeMessage($message);
        $request->merge([
            'student_id' => $message->student_id,
            'teacher_assignment_id' => $message->teacher_assignment_id,
            'parent_message_id' => $message->id,
        ]);
        return $this->send($request);
    }

    public function destroy(TeacherMessage $message)
    {
        $this->authorizeMessage($message);
        if (Schema::hasColumn('teacher_messages', 'deleted_by_teacher_at')) {
            $message->update(['deleted_by_teacher_at' => now()]);
        } elseif ((int) $message->teacher_id === (int) auth()->id() && $message->isFromTeacher()) {
            $message->delete();
        }
        return redirect()->route('teacher.messages.index', ['student' => $message->student_id])->with('success', 'Message supprimé de votre affichage.');
    }

    public function attachment(Request $request, TeacherMessage $message)
    {
        $this->authorizeMessage($message);
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

    protected function assignments(): Collection
    {
        if (!Schema::hasTable('teacher_assignments')) return collect();
        return TeacherAssignment::query()
            ->with(['schoolClass', 'subject', 'teacher'])
            ->where('teacher_id', auth()->id())
            ->where('is_active', true)
            ->get();
    }

    protected function studentsForAssignments(Collection $assignments): Collection
    {
        if ($assignments->isEmpty() || !Schema::hasTable('student_profiles')) return collect();
        $classIds = $assignments->pluck('school_class_id')->filter()->unique()->values();
        return User::query()
            ->with(['studentProfile.schoolClass'])
            ->whereHas('studentProfile', fn ($query) => $query->whereIn('school_class_id', $classIds))
            ->orderBy('full_name')
            ->orderBy('name')
            ->get();
    }

    protected function messagesForAssignments(Collection $assignments): Collection
    {
        if (!Schema::hasTable('teacher_messages') || $assignments->isEmpty()) return collect();
        return TeacherMessage::query()
            ->with(['student.studentProfile.schoolClass', 'subject', 'schoolClass', 'teacher', 'parentMessage'])
            ->where('teacher_id', auth()->id())
            ->whereIn('school_class_id', $assignments->pluck('school_class_id')->filter()->unique())
            ->when(Schema::hasColumn('teacher_messages', 'deleted_by_teacher_at'), fn ($query) => $query->whereNull('deleted_by_teacher_at'))
            ->orderBy('created_at')
            ->get();
    }

    protected function authorizedStudent(int $studentId, Collection $assignments): User
    {
        $students = $this->studentsForAssignments($assignments)->keyBy('id');
        abort_unless($students->has($studentId), 403);
        return $students[$studentId];
    }

    protected function assignmentForStudent(User $student, Collection $assignments, int $preferredId = 0): ?TeacherAssignment
    {
        $studentClassId = (int) optional($student->studentProfile)->school_class_id;
        if ($preferredId) {
            $preferred = $assignments->firstWhere('id', $preferredId);
            if ($preferred && (int) $preferred->school_class_id === $studentClassId) return $preferred;
        }
        return $assignments->firstWhere('school_class_id', $studentClassId) ?: $assignments->first();
    }

    protected function storeAttachment(Request $request): array
    {
        if (!$request->hasFile('attachment')) return [null, null, null, null];
        $file = $request->file('attachment');
        return [$file->store('teacher_messages', 'local'), $file->getClientOriginalName(), $file->getMimeType(), $file->getSize()];
    }

    protected function authorizeMessage(TeacherMessage $message): void
    {
        abort_unless((int) $message->teacher_id === (int) auth()->id(), 403);
    }

    protected function threadKey(TeacherMessage $message): string
    {
        return (string) $message->teacher_assignment_id . '-' . (string) $message->student_id;
    }
}
