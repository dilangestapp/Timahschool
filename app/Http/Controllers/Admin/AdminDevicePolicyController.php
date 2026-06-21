<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminDevicePolicyController extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasColumn('users', 'device_policy')) {
            return response($this->htmlPage(
                'Gestion multi-appareils',
                '<div class="alert error"><strong>Migration non appliquée.</strong><br>La colonne <code>device_policy</code> n’existe pas encore. Déploie la dernière version depuis GitHub puis lance <code>php artisan migrate --force</code> sur le VPS Contabo.</div>'
            ));
        }

        $search = trim((string) $request->query('q', ''));

        $users = User::query()
            ->with(['studentProfile.schoolClass'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', '%' . $search . '%')
                        ->orWhere('full_name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('username', 'like', '%' . $search . '%');
                });
            })
            ->where(function ($query) {
                $query->whereHas('studentProfile')
                    ->orWhereHas('roles', function ($roleQuery) {
                        $roleQuery->whereRaw('LOWER(name) IN (?, ?, ?)', ['student', 'eleve', 'élève']);
                    });
            })
            ->orderByDesc('id')
            ->limit(80)
            ->get();

        $rows = $users->map(function (User $user) {
            $name = e($user->full_name ?: $user->name ?: $user->username ?: ('Utilisateur #' . $user->id));
            $phone = e($user->phone ?: 'Sans téléphone');
            $class = e($user->studentProfile?->schoolClass?->name ?: 'Classe non définie');
            $policy = (string) ($user->device_policy ?: 'single');
            $badge = match ($policy) {
                'demo_student' => '<span class="badge demo">Élève test multi-appareils</span>',
                'multiple' => '<span class="badge multi">Multi-appareils interne</span>',
                default => '<span class="badge single">Appareil unique</span>',
            };

            $options = [
                'single' => 'Appareil unique',
                'demo_student' => 'Élève test multi-appareils',
                'multiple' => 'Multi-appareils interne',
            ];

            $select = '<select name="device_policy">';
            foreach ($options as $value => $label) {
                $selected = $policy === $value ? ' selected' : '';
                $select .= '<option value="' . e($value) . '"' . $selected . '>' . e($label) . '</option>';
            }
            $select .= '</select>';

            return '<article class="row"><div><strong>' . $name . '</strong><p>' . $phone . ' · ' . $class . '</p>' . $badge . '</div><form method="POST" action="' . e(route('admin.device-policy.update', $user->id)) . '">' . csrf_field() . $select . '<button>Enregistrer</button></form></article>';
        })->implode('');

        if ($rows === '') {
            $rows = '<div class="empty">Aucun élève trouvé.</div>';
        }

        $content = '
            <div class="topbar">
                <div>
                    <h1>Comptes élèves multi-appareils</h1>
                    <p>Active ici les comptes élèves de test pour les partenaires, sans limite d’utilisation.</p>
                </div>
                <a class="btn secondary" href="/backoffice-access/dashboard">Dashboard</a>
            </div>
            ' . (session('success') ? '<div class="alert success">' . e(session('success')) . '</div>' : '') . '
            ' . (session('error') ? '<div class="alert error">' . e(session('error')) . '</div>' : '') . '
            <section class="info">
                <h2>Règle de sécurité</h2>
                <div class="cards">
                    <div><b>Appareil unique</b><span>Pour les vrais élèves abonnés : 1 compte = 1 appareil.</span></div>
                    <div><b>Élève test multi-appareils</b><span>Pour les partenaires qui testent comme élève sur plusieurs téléphones.</span></div>
                    <div><b>Multi-appareils interne</b><span>Pour les comptes de démonstration internes ou supervision.</span></div>
                </div>
            </section>
            <form class="search" method="GET"><input name="q" value="' . e($search) . '" placeholder="Rechercher par nom, téléphone, email..."><button>Rechercher</button><a href="' . e(route('admin.device-policy.index')) . '">Réinitialiser</a></form>
            <section class="list">' . $rows . '</section>
        ';

        return response($this->htmlPage('Comptes élèves multi-appareils', $content));
    }

    public function update(Request $request, int $userId)
    {
        if (!Schema::hasColumn('users', 'device_policy')) {
            return back()->with('error', 'Migration non appliquée : colonne device_policy introuvable.');
        }

        $data = $request->validate([
            'device_policy' => ['required', 'string', 'in:single,demo_student,multiple'],
        ]);

        $user = User::query()->findOrFail($userId);
        $user->forceFill(['device_policy' => $data['device_policy']])->save();

        if ($data['device_policy'] === 'single' && Schema::hasTable('mobile_devices')) {
            $latestDeviceId = DB::table('mobile_devices')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->orderByDesc('last_seen_at')
                ->orderByDesc('id')
                ->value('id');

            if ($latestDeviceId) {
                DB::table('mobile_devices')
                    ->where('user_id', $user->id)
                    ->where('status', 'active')
                    ->where('id', '<>', $latestDeviceId)
                    ->update(['status' => 'replaced', 'replaced_at' => now(), 'updated_at' => now()]);
            }
        }

        $label = match ($data['device_policy']) {
            'demo_student' => 'Élève test multi-appareils',
            'multiple' => 'Multi-appareils interne',
            default => 'Appareil unique',
        };

        return back()->with('success', 'Politique appareil mise à jour : ' . $label . '.');
    }

    private function htmlPage(string $title, string $content): string
    {
        return '<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . e($title) . '</title><style>body{font-family:Arial,Helvetica,sans-serif;background:#f5f7fb;color:#0f172a;margin:0}.wrap{max-width:1100px;margin:28px auto;padding:18px}.topbar{display:flex;justify-content:space-between;gap:14px;align-items:center;margin-bottom:18px}h1{margin:0;font-size:30px}p{color:#64748b}.btn,.search button,.row button{background:#2563eb;color:#fff;border:0;border-radius:12px;padding:11px 14px;text-decoration:none;font-weight:800;cursor:pointer}.secondary{background:#fff;color:#0f172a;border:1px solid #dbe3ef}.alert{padding:14px;border-radius:16px;margin:12px 0;font-weight:700}.success{background:#ecfdf5;color:#047857;border:1px solid #a7f3d0}.error{background:#fef2f2;color:#b91c1c;border:1px solid #fecaca}.info,.list,.search{background:white;border:1px solid #dbe3ef;border-radius:22px;padding:18px;margin:14px 0;box-shadow:0 14px 32px rgba(15,23,42,.08)}.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}.cards div{background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:14px}.cards b,.cards span{display:block}.cards span{color:#64748b;margin-top:6px;line-height:1.45}.search{display:flex;gap:10px;align-items:center}.search input{flex:1;padding:12px;border:1px solid #cbd5e1;border-radius:12px}.search a{color:#2563eb;font-weight:800}.row{display:flex;justify-content:space-between;gap:14px;align-items:center;border-bottom:1px solid #e5edf7;padding:14px 0}.row:last-child{border-bottom:0}.row strong{font-size:17px}.row p{margin:5px 0}.row form{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.row select{padding:10px;border:1px solid #cbd5e1;border-radius:12px}.badge{display:inline-block;padding:6px 9px;border-radius:999px;font-size:12px;font-weight:800}.single{background:#eef2ff;color:#3730a3}.demo{background:#dcfce7;color:#166534}.multi{background:#fef3c7;color:#92400e}.empty{padding:24px;text-align:center;color:#64748b}@media(max-width:760px){.topbar,.row,.search{display:block}.cards{grid-template-columns:1fr}.row form{margin-top:12px}.search input{width:100%;box-sizing:border-box;margin-bottom:8px}}</style></head><body><div class="wrap">' . $content . '</div></body></html>';
    }
}
