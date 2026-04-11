<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersTableColumns;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    use FiltersTableColumns;

    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $status = trim((string) $request->get('status', ''));
        $tableMissing = !$this->hasTableSafe('payments');

        $items = $tableMissing ? collect() : Payment::query()
            ->with(['user', 'plan'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    foreach (['notchpay_reference', 'phone_number', 'status', 'failure_reason'] as $column) {
                        if ($this->hasColumnSafe('payments', $column)) {
                            $sub->orWhere($column, 'like', '%' . $search . '%');
                        }
                    }
                })->orWhereHas('user', function ($sub) use ($search) {
                    foreach (['name', 'full_name', 'username', 'email'] as $column) {
                        $sub->orWhere($column, 'like', '%' . $search . '%');
                    }
                });
            })
            ->when($status !== '' && $this->hasColumnSafe('payments', 'status'), fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();

        $summary = [
            'total' => $items->count(),
            'completed' => $items->whereIn('status', ['completed', 'paid', 'success'])->count(),
            'pending' => $items->where('status', 'pending')->count(),
            'failed' => $items->where('status', 'failed')->count(),
            'revenue' => $items->whereIn('status', ['completed', 'paid', 'success'])->sum('amount'),
        ];

        return view('admin.payments.index', compact('search', 'status', 'tableMissing', 'items', 'summary'));
    }
}
