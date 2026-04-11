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
                'messages' => collect(),
                'paginator' => null,
                'filters' => $request->only('status'),
            ]);
        }

        $query = TeacherMessage::query()
            ->with(['student.studentProfile.schoolClass', 'subject', 'schoolClass'])
            ->where('teacher_id', auth()->id());

        if ($request->filled('status')) {
            if ($request->status === TeacherMessage::STATUS_UNREAD) {
                $query->where('status', TeacherMessage::STATUS_UNREAD);
            } elseif ($request->status === TeacherMessage::STATUS_REPLIED) {
                $query->where('status', TeacherMessage::STATUS_REPLIED);
            } elseif ($request->status === TeacherMessage::STATUS_READ) {
                $query->where('status', TeacherMessage::STATUS_READ);
            }
        }

        $paginator = $query->latest()->paginate(12)->withQueryString();

        return view('teacher.messages.index', [
            'messages' => collect($paginator->items()),
            'paginator' => $paginator,
            'filters' => $request->only('status'),
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

        $message->load(['student.studentProfile.schoolClass', 'subject', 'schoolClass', 'teacher']);

        return view('teacher.messages.show', compact('message'));
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

        return redirect()->route('teacher.messages.show', $message)->with('success', 'Réponse envoyée avec succès.');
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

        if ($message->isImageAttachment()) {
            return response()->file($absolutePath);
        }

        return response()->download($absolutePath, $downloadName);
    }

    protected function authorizeMessage(TeacherMessage $message): void
    {
        abort_unless((int) $message->teacher_id === (int) auth()->id(), 403);
    }
}
