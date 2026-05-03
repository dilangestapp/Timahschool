<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DigitalBoardPost;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminDigitalBoardController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $tableMissing = !Schema::hasTable('digital_board_posts');

        $items = $tableMissing
            ? collect()
            : DigitalBoardPost::query()
                ->with(['author', 'schoolClass'])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('content', 'like', '%' . $search . '%')
                        ->orWhere('type', 'like', '%' . $search . '%')
                        ->orWhere('audience', 'like', '%' . $search . '%');
                })
                ->latest('published_at')
                ->get();

        $classes = Schema::hasTable('school_classes')
            ? SchoolClass::query()->orderBy('order')->orderBy('name')->get()
            : collect();

        return view('admin.digital-board.index', compact('items', 'classes', 'search', 'tableMissing'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'type' => ['required', 'string', 'max:60'],
            'audience' => ['required', 'string', 'max:60'],
            'school_class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'status' => ['required', 'string', 'max:40'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $data['author_id'] = auth()->id();
        if (($data['status'] ?? '') === DigitalBoardPost::STATUS_PUBLISHED && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        DigitalBoardPost::query()->create($data);

        return back()->with('success', 'Publication ajoutée au babillard numérique.');
    }

    public function publish(DigitalBoardPost $post)
    {
        $post->update([
            'status' => DigitalBoardPost::STATUS_PUBLISHED,
            'published_at' => $post->published_at ?: now(),
        ]);

        return back()->with('success', 'Publication mise en ligne.');
    }

    public function archive(DigitalBoardPost $post)
    {
        $post->update(['status' => DigitalBoardPost::STATUS_ARCHIVED]);

        return back()->with('success', 'Publication archivée.');
    }

    public function delete(DigitalBoardPost $post)
    {
        $post->delete();

        return back()->with('success', 'Publication supprimée.');
    }
}
