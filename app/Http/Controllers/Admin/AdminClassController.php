<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersTableColumns;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminClassController extends Controller
{
    use FiltersTableColumns;

    protected array $levels = [
        'enseignement_general' => 'Enseignement général',
        'enseignement_technique' => 'Enseignement technique',
    ];

    public function index()
    {
        $tableMissing = !$this->hasTableSafe('school_classes');
        $classes = $tableMissing
            ? collect()
            : SchoolClass::query()->orderBy('level')->orderBy('order')->orderBy('name')->get();

        return view('admin.classes.index', [
            'tableMissing' => $tableMissing,
            'classes' => $classes,
            'levels' => $this->levels,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('school_classes', 'name')],
            'level' => ['required', Rule::in(array_keys($this->levels))],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'level' => $request->level,
            'order' => (int) ($request->order ?? 0),
            'is_active' => $request->boolean('is_active'),
        ];

        SchoolClass::query()->create($this->onlyExistingColumns('school_classes', $data));

        return back()->with('success', 'Classe ajoutée avec succès.');
    }

    public function update(Request $request, int $id)
    {
        $class = SchoolClass::query()->findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('school_classes', 'name')->ignore($class->id)],
            'level' => ['required', Rule::in(array_keys($this->levels))],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'level' => $request->level,
            'order' => (int) ($request->order ?? 0),
            'is_active' => $request->boolean('is_active'),
        ];

        $class->update($this->onlyExistingColumns('school_classes', $data));

        return back()->with('success', 'Classe mise à jour.');
    }

    public function delete(int $id)
    {
        $class = SchoolClass::query()->findOrFail($id);
        $class->delete();

        return back()->with('success', 'Classe supprimée.');
    }
}
