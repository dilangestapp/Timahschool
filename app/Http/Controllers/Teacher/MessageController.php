<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasTable('teacher_messages')) {
            return view('teacher.messages.index', [
                'threads' => collect(),
                'selectedThreadKey' => null,
            ]);
        }

        $messages = TeacherMessage::query()
            ->with(['student.studentProfile.schoolClass', 'subject', 'schoolClass', 'teacher'])
            ->where('teacher_id', auth()->id())
            ->orderBy('created_at')
            ->get();

        $threads = $messages
            ->groupBy(function (TeacherMessage $message) {
                return $this->threadKey($message);
            })
            ->map(function (Collection $conversation, string $threadKey) {
                $conversation = $conversation->sortBy('created_at')->values();
                $latest = $conversation->last();
                $first = $conversation->first();

                $replyTarget = $conversation
                    ->reverse()
                    ->first(fn (TeacherMessage $message) => empty($message->reply_message)) ?? $latest;

                return (object) [
                    'thread_key' => $threadKey,
                    'student' => $first?->student,
                    'subject' => $first?->subject,
                    'schoolClass' => $first?->schoolClass,
                    'messages' => $conversation,
                    'latest_message' => $latest,
                    'reply_target' => $replyTarget,
                    'unread_count' => $conversation->where('status', TeacherMessage::STATUS_UNREAD)->count(),
                    'attachment_count' => $conversation->filter(fn (TeacherMessage $message) => !empty($message->attachment_path))->count(),
                    'sort_timestamp' => $latest && $latest->created_at ? $latest->created_at->timestamp : 0,
                ];
            })
            ->sort(function ($a, $b) {
                if ($a->sort_timestamp === $b->sort_timestamp) {
                    $aName = strtolower((string) ($a->student->full_name ?? $a->student->name ?? $a->student->username ?? ''));
                    $bName = strtolower((string) ($b->student->full_name ?? $b->student->name ?? $b->student->username ?? ''));

                    return $aName <=> $bName;
                }

                return $b->sort_timestamp <=> $a->sort_timestamp;
            })
            ->values();

        $requestedThreadKey = (string) $request->query('thread');
        $selectedThreadKey = $threads->contains(fn ($thread) => $thread->thread_key === $requestedThreadKey)
            ? $requestedThreadKey
            : optional($threads->first())->thread_key;

        return view('teacher.messages.index', [
            'threads' => $threads,
            'selectedThreadKey' => $selectedThreadKey,
        ]);
    }

    public function show(TeacherMessage $message)
    {
        $this->authorizeMessage($message);

        if ($message->status === TeacherMessage::STATUS_UNREAD) {
            $message->update([
                'status' => TeacherMessage::STATUS_READ,
                'read_at' => now(),
            ]);
        }

        return redirect()->route('teacher.messages.index', [
            'thread' => $this->threadKey($message),
        ]);
    }

    public function reply(Request $request, TeacherMessage $message)
    {
        $this->authorizeMessage($message);

        $data = $request->validate([
            'reply_message' => ['required', 'string'],
        ]);

        $message->update([
            'reply_message' => $data['reply_message'],
            'replied_at' => now(),
            'status' => TeacherMessage::STATUS_REPLIED,
            'read_at' => $message->read_at ?? now(),
        ]);

        return redirect()->route('teacher.messages.index', [
            'thread' => $this->threadKey($message),
        ])->with('success', 'Réponse envoyée avec succès.');
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

    protected function authorizeMessage(TeacherMessage $message): void
    {
        abort_unless((int) $message->teacher_id === (int) auth()->id(), 403);
    }

    protected function threadKey(TeacherMessage $message): string
    {
        return (string) $message->teacher_assignment_id . '-' . (string) $message->student_id;
    }
}
