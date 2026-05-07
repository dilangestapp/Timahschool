<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDatabaseBackupController extends Controller
{
    public function index()
    {
        $tables = $this->tables();
        $downloadUrl = route('admin.database-backup.download');
        $db = config('database.connections.mysql.database');
        $chips = collect($tables)->map(fn ($t) => '<span class="chip">' . e($t) . '</span>')->implode('');

        return response('<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sauvegarde TIMAH ACADEMY</title><style>body{font-family:Arial,sans-serif;background:#f5f7fb;color:#0f172a;margin:0}.wrap{max-width:980px;margin:35px auto;padding:18px}.card{background:#fff;border:1px solid #dbe3ef;border-radius:24px;padding:24px;box-shadow:0 18px 42px #0f172a1a}.btn{display:inline-block;background:#2563eb;color:#fff;padding:14px 18px;border-radius:14px;text-decoration:none;font-weight:800}.warn{background:#fff7ed;border:1px solid #fdba74;color:#9a3412;padding:14px;border-radius:16px;margin:18px 0}.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin:18px 0}.stat{background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:14px}.stat b{display:block;font-size:22px}.chip{display:inline-block;margin:4px;padding:7px 10px;background:#eef2ff;border-radius:999px;font-size:12px;color:#1e3a8a}.muted{color:#64748b;line-height:1.6}@media(max-width:700px){.grid{grid-template-columns:1fr}.wrap{margin:8px auto}}</style></head><body><div class="wrap"><div class="card"><h1>Sauvegarde base de données</h1><p class="muted">Télécharge une copie SQL complète de la base actuelle. Elle permettra de restaurer TIMAH ACADEMY si Railway suspend le service, si la base est perdue, ou si tu changes d’hébergeur.</p><div class="grid"><div class="stat"><b>' . e((string) $db) . '</b>Base actuelle</div><div class="stat"><b>' . count($tables) . '</b>Tables détectées</div><div class="stat"><b>' . now()->format('d/m/Y H:i') . '</b>Date</div></div><div class="warn"><strong>Important :</strong> garde ce fichier privé. Il peut contenir les comptes, téléphones, abonnements, TD et mots de passe chiffrés.</div><a class="btn" href="' . e($downloadUrl) . '">Télécharger la sauvegarde SQL</a><p class="muted">Fais une sauvegarde après chaque gros import de TD et au minimum chaque soir pendant la phase de test.</p><h3>Tables incluses</h3><div>' . $chips . '</div><p><a href="/backoffice-access/dashboard">Retour au dashboard</a></p></div></div></body></html>');
    }

    public function download(): StreamedResponse
    {
        $fileName = 'timah_academy_backup_' . now()->format('Y_m_d_His') . '.sql';
        return response()->streamDownload(function () {
            $pdo = DB::connection()->getPdo();
            $this->out('-- TIMAH ACADEMY DATABASE BACKUP');
            $this->out('-- Generated at ' . now()->format('Y-m-d H:i:s'));
            $this->out('SET FOREIGN_KEY_CHECKS=0;');
            $this->out('SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";');
            $this->out('');

            foreach ($this->tables() as $table) {
                $qt = $this->qi($table);
                $this->out('-- TABLE ' . $qt);
                $this->out('DROP TABLE IF EXISTS ' . $qt . ';');
                $create = DB::selectOne('SHOW CREATE TABLE ' . $qt);
                $vals = array_values((array) $create);
                $this->out(($vals[1] ?? '') . ';');
                $this->out('');

                DB::table($table)->orderByRaw('1')->chunk(200, function ($rows) use ($pdo, $qt) {
                    foreach ($rows as $row) {
                        $data = (array) $row;
                        $cols = array_map(fn ($c) => $this->qi($c), array_keys($data));
                        $values = array_map(function ($v) use ($pdo) {
                            if ($v === null) return 'NULL';
                            if (is_bool($v)) return $v ? '1' : '0';
                            return $pdo->quote((string) $v);
                        }, array_values($data));
                        $this->out('INSERT INTO ' . $qt . ' (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $values) . ');');
                    }
                });
                $this->out('');
            }

            $this->out('SET FOREIGN_KEY_CHECKS=1;');
            $this->out('-- END OF BACKUP');
        }, $fileName, ['Content-Type' => 'application/sql; charset=UTF-8']);
    }

    private function tables(): array
    {
        return collect(DB::select('SHOW FULL TABLES WHERE Table_type = ?', ['BASE TABLE']))
            ->map(fn ($r) => array_values((array) $r)[0] ?? null)
            ->filter()->values()->all();
    }

    private function qi(string $id): string
    {
        return '`' . str_replace('`', '``', $id) . '`';
    }

    private function out(string $line = ''): void
    {
        echo $line . "\n";
        if (ob_get_level() > 0) @ob_flush();
        flush();
    }
}
