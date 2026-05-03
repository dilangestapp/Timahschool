<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MobileDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminMobileDeviceController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $tableMissing = !Schema::hasTable('mobile_devices');

        $items = $tableMissing
            ? collect()
            : MobileDevice::query()
                ->with('user')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($sub) use ($search) {
                        $sub->where('phone', 'like', '%' . $search . '%')
                            ->orWhere('device_name', 'like', '%' . $search . '%')
                            ->orWhere('device_model', 'like', '%' . $search . '%')
                            ->orWhere('status', 'like', '%' . $search . '%')
                            ->orWhereHas('user', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'like', '%' . $search . '%')
                                    ->orWhere('full_name', 'like', '%' . $search . '%')
                                    ->orWhere('username', 'like', '%' . $search . '%')
                                    ->orWhere('phone', 'like', '%' . $search . '%');
                            });
                    });
                })
                ->latest('last_seen_at')
                ->get();

        return view('admin.mobile-devices.index', compact('items', 'search', 'tableMissing'));
    }

    public function replace(Request $request, MobileDevice $device)
    {
        $device->update([
            'status' => MobileDevice::STATUS_REPLACED,
            'replaced_at' => now(),
            'admin_note' => trim((string) $request->get('admin_note')) ?: 'Appareil réinitialisé par l’administration pour autoriser un transfert.',
        ]);

        return back()->with('success', 'Appareil réinitialisé. L’utilisateur pourra activer un nouveau téléphone à la prochaine connexion.');
    }

    public function block(Request $request, MobileDevice $device)
    {
        $device->update([
            'status' => MobileDevice::STATUS_BLOCKED,
            'blocked_at' => now(),
            'admin_note' => trim((string) $request->get('admin_note')) ?: 'Appareil bloqué par l’administration.',
        ]);

        return back()->with('success', 'Appareil bloqué.');
    }
}
