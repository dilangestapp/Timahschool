<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TeacherAssignment;
use App\Models\TeacherMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function index()
    {
        $messages = Schema::hasTable('teacher_messages')
            ? TeacherMessage::query()
                ->with(['teacher', 'subject', 'schoolClass'])
                ->where('student_id', auth()->id())
                ->latest()
                ->get()
            : collect();

        return view('student.messages.index', compact('messages'));
    }

    public function create()
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

        return view('student.messages.create', compact('assignments', 'studentProfile'));
    }

    public function store(Request $request)
    {
        $student = auth()->user();
        $studentProfile = $student->studentProfile;

        abort_unless($studentProfile && $studentProfile->school_class_id, 403, 'Aucune classe élève trouvée.');

        $data = $request->validate([
            'teacher_assignment_id' => ['required', 'integer', 'exists:teacher_assignments,id'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $assignment = TeacherAssignment::query()
            ->where('id', $data['teacher_assignment_id'])
            ->where('school_class_id', $studentProfile->school_class_id)
            ->where('is_active', true)
            ->firstOrFail();

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('teacher_messages', 'local');
            $attachmentName = $request->file('attachment')->getClientOriginalName();
        }

        $payload = [
            'teacher_assignment_id' => $assignment->id,
            'teacher_id' => $assignment->teacher_id,
            'student_id' => $student->id,
            'school_class_id' => $assignment->school_class_id,
            'subject_id' => $assignment->subject_id,
            'title' => $data['title'],
            'message' => $data['message'],
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'status' => TeacherMessage::STATUS_UNREAD,
        ];

        if (Schema::hasColumn('teacher_messages', 'topic')) {
            $payload['topic'] = $data['title'];
        }

        TeacherMessage::query()->create($payload);

        return redirect()->route('student.messages.index')->with('success', 'Message envoyé à l\'enseignant.');
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

        if ($message->isImageAttachment()) {
            return response()->file($absolutePath);
        }

        return response()->download($absolutePath, $downloadName);
    }
}
