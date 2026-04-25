<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersTableColumns;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminSubscriptionController extends Controller
{
    use FiltersTableColumns;

    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $tableMissing = !$this->hasTableSafe('subscriptions');

        $items = $tableMissing
            ? collect()
            : Subscription::query()
                ->with(['user', 'plan'])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($searchQuery) use ($search) {
                        $searchQuery->where('status', 'like', '%' . $search . '%')
                            ->orWhere('plan_name', 'like', '%' . $search . '%')
                            ->orWhereHas('user', function ($sub) use ($search) {
                                $sub->where('name', 'like', '%' . $search . '%')
                                    ->orWhere('full_name', 'like', '%' . $search . '%')
                                    ->orWhere('username', 'like', '%' . $search . '%')
                                    ->orWhere('email', 'like', '%' . $search . '%');
                            });
                    });
                })
                ->latest()
                ->get();

        $plans = Schema::hasTable('subscription_plans')
            ? SubscriptionPlan::query()->orderBy('is_featured', 'desc')->orderBy('order')->orderBy('name')->get()
            : collect();

        return view('admin.subscriptions.index', compact('search', 'tableMissing', 'items', 'plans'));
    }

    public function update(Request $request, int $id)
    {
        $subscription = Subscription::query()->findOrFail($id);

        $request->validate([
            'subscription_plan_id' => ['nullable', 'integer', 'exists:subscription_plans,id'],
            'plan_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:50'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'cancellation_reason' => ['nullable', 'string'],
        ]);

        $plan = null;
        if ($request->filled('subscription_plan_id') && Schema::hasTable('subscription_plans')) {
            $plan = SubscriptionPlan::query()->find($request->integer('subscription_plan_id'));
        }

        $planName = $plan?->name ?: trim((string) $request->plan_name);
        if ($planName === '') {
            $planName = $subscription->plan_name ?: 'Abonnement manuel';
        }

        $data = [
            'subscription_plan_id' => $plan?->id,
            'plan_name' => $planName,
            'status' => $request->status,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'is_trial' => $request->boolean('is_trial') || $request->status === Subscription::STATUS_TRIAL,
            'cancellation_reason' => $request->cancellation_reason,
            'cancelled_at' => in_array($request->status, [Subscription::STATUS_CANCELLED, Subscription::STATUS_FAILED], true) ? now() : null,
        ];

        $subscription->update($this->onlyExistingColumns('subscriptions', $data));

        return back()->with('success', 'Abonnement mis à jour manuellement.');
    }

    public function delete(int $id)
    {
        Subscription::query()->findOrFail($id)->delete();

        return back()->with('success', 'Abonnement supprimé.');
    }
}
