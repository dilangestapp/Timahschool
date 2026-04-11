<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersTableColumns;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminSubjectController extends Controller
{
    use FiltersTableColumns;

    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $tableMissing = !$this->hasTableSafe('subjects');

        $subjects = $tableMissing
            ? collect()
            : Subject::query()
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($sub) use ($search) {
                        $sub->where('name', 'like', '%' . $search . '%')
                            ->orWhere('description', 'like', '%' . $search . '%')
                            ->orWhere('slug', 'like', '%' . $search . '%');
                    });
                })
                ->orderBy('order')
                ->orderBy('name')
                ->get();

        return view('admin.subjects.index', compact('search', 'tableMissing', 'subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('subjects', 'name')],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:30'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon,
            'color' => $request->color ?: '#2563eb',
            'order' => (int) ($request->order ?? 0),
            'is_active' => $request->boolean('is_active'),
        ];

        Subject::query()->create($this->onlyExistingColumns('subjects', $data));

        return back()->with('success', 'Matière ajoutée avec succès.');
    }

    public function update(Request $request, int $id)
    {
        $subject = Subject::query()->findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('subjects', 'name')->ignore($subject->id)],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:30'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon,
            'color' => $request->color ?: '#2563eb',
            'order' => (int) ($request->order ?? 0),
            'is_active' => $request->boolean('is_active'),
        ];

        $subject->update($this->onlyExistingColumns('subjects', $data));

        return back()->with('success', 'Matière mise à jour.');
    }

    public function delete(int $id)
    {
        Subject::query()->findOrFail($id)->delete();

        return back()->with('success', 'Matière supprimée.');
    }
}
