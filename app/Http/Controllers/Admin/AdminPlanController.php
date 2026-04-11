<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersTableColumns;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminPlanController extends Controller
{
    use FiltersTableColumns;

    public function index()
    {
        $tableMissing = !$this->hasTableSafe('subscription_plans');
        $plans = $tableMissing
            ? collect()
            : SubscriptionPlan::query()->orderBy('order')->orderBy('price')->get();

        return view('admin.plans.index', compact('tableMissing', 'plans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('subscription_plans', 'name')],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'duration_unit' => ['required', Rule::in(['day', 'week', 'month', 'year'])],
            'duration_value' => ['required', 'integer', 'min:1'],
            'features' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'currency' => strtoupper((string) $request->currency),
            'duration_unit' => $request->duration_unit,
            'duration_value' => (int) $request->duration_value,
            'features' => $this->normalizeFeatures($request->features),
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
            'order' => (int) ($request->order ?? 0),
        ];

        SubscriptionPlan::query()->create($this->onlyExistingColumns('subscription_plans', $data));

        return back()->with('success', 'Plan créé avec succès.');
    }

    public function update(Request $request, int $id)
    {
        $plan = SubscriptionPlan::query()->findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('subscription_plans', 'name')->ignore($plan->id)],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'duration_unit' => ['required', Rule::in(['day', 'week', 'month', 'year'])],
            'duration_value' => ['required', 'integer', 'min:1'],
            'features' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'currency' => strtoupper((string) $request->currency),
            'duration_unit' => $request->duration_unit,
            'duration_value' => (int) $request->duration_value,
            'features' => $this->normalizeFeatures($request->features),
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
            'order' => (int) ($request->order ?? 0),
        ];

        $plan->update($this->onlyExistingColumns('subscription_plans', $data));

        return back()->with('success', 'Plan mis à jour.');
    }

    public function delete(int $id)
    {
        SubscriptionPlan::query()->findOrFail($id)->delete();

        return back()->with('success', 'Plan supprimé.');
    }

    protected function normalizeFeatures(?string $raw): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $raw))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
