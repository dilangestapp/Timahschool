<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TdQuestionMessage;
use App\Models\TdQuestionThread;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class TdQuestionController extends Controller
{
    public function index(Request $request)
    {
        $assignments = $this->assignments();
        $query = TdQuestionThread::query()->with(['tdSet', 'student', 'schoolClass', 'subject', 'messages.sender']);
        $this->applyAssignments($query, $assignments);

        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }

        $threads = $query->latest('last_message_at')->paginate(12)->withQueryString();

        return view('teacher.td.questions.index', [
            'threads' => $threads,
            'selected' => null,
            'filters' => $request->only('status'),
        ]);
    }

    public function show(TdQuestionThread $thread)
    {
        $this->authorizeThread($thread);
        $thread->load(['tdSet', 'student', 'schoolClass', 'subject', 'messages.sender']);

        return view('teacher.td.questions.index', [
            'threads' => TdQuestionThread::query()->with(['tdSet', 'student', 'schoolClass', 'subject'])->where('teacher_id', auth()->id())->latest('last_message_at')->paginate(12),
            'selected' => $thread,
            'filters' => [],
        ]);
    }

    public function reply(Request $request, TdQuestionThread $thread)
    {
        $this->authorizeThread($thread);

        $data = $request->validate([
            'message_html' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,txt'],
        ]);

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
            'sender_id' => auth()->id(),
            'sender_role' => 'teacher',
            'message_html' => $data['message_html'],
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_mime' => $attachmentMime,
            'is_read' => false,
        ]);

        $thread->update([
            'status' => TdQuestionThread::STATUS_ANSWERED,
            'last_message_at' => now(),
        ]);

        return redirect()->route('teacher.td.questions.show', $thread)->with('success', 'Réponse envoyée.');
    }

    public function attachment(TdQuestionMessage $message)
    {
        $thread = $message->thread;
        abort_unless($thread && (int) $thread->teacher_id === (int) auth()->id(), 403);
        abort_unless($message->attachment_path, 404);

        return Storage::disk('public')->response($message->attachment_path, $message->attachment_name ?: basename($message->attachment_path));
    }

    protected function assignments(): Collection
    {
        if (!Schema::hasTable('teacher_assignments')) {
            return collect();
        }

        return TeacherAssignment::query()
            ->where('teacher_id', auth()->id())
            ->where('is_active', true)
            ->get();
    }

    protected function applyAssignments($query, Collection $assignments): void
    {
        if ($assignments->isEmpty()) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where(function ($builder) use ($assignments) {
            foreach ($assignments as $assignment) {
                $builder->orWhere(function ($inner) use ($assignment) {
                    $inner->where('school_class_id', $assignment->school_class_id)
                        ->where('subject_id', $assignment->subject_id)
                        ->where('teacher_id', auth()->id());
                });
            }
        });
    }

    protected function authorizeThread(TdQuestionThread $thread): void
    {
        abort_unless((int) $thread->teacher_id === (int) auth()->id(), 403);
    }
}
