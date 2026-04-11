<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersTableColumns;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;

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
                    $query->where('status', 'like', '%' . $search . '%')
                        ->orWhere('plan_name', 'like', '%' . $search . '%')
                        ->orWhereHas('user', function ($sub) use ($search) {
                            $sub->where('name', 'like', '%' . $search . '%')
                                ->orWhere('full_name', 'like', '%' . $search . '%')
                                ->orWhere('username', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        });
                })
                ->latest()
                ->get();

        return view('admin.subscriptions.index', compact('search', 'tableMissing', 'items'));
    }

    public function update(Request $request, int $id)
    {
        $subscription = Subscription::query()->findOrFail($id);

        $request->validate([
            'status' => ['required', 'string', 'max:50'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'cancellation_reason' => ['nullable', 'string'],
        ]);

        $data = [
            'status' => $request->status,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'is_trial' => $request->boolean('is_trial'),
            'cancellation_reason' => $request->cancellation_reason,
            'cancelled_at' => in_array($request->status, [Subscription::STATUS_CANCELLED, Subscription::STATUS_FAILED], true) ? now() : null,
        ];

        $subscription->update($this->onlyExistingColumns('subscriptions', $data));

        return back()->with('success', 'Abonnement mis à jour.');
    }

    public function delete(int $id)
    {
        Subscription::query()->findOrFail($id)->delete();

        return back()->with('success', 'Abonnement supprimé.');
    }
}
