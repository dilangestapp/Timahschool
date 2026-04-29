<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

class ImportOldRailwayDatabase extends Command
{
    protected $signature = 'timah:import-old-db {--force : Confirmer l\'import sans interaction}';

    protected $description = 'Importe les données de l\'ancienne base Railway vers la base active.';

    public function handle(): int
    {
        $oldUrl = env('OLD_DATABASE_URL');

        if (!$oldUrl) {
            $this->error('OLD_DATABASE_URL est absent. Ajoutez la variable dans Railway avant de relancer.');
            return self::FAILURE;
        }

        if (!$this->option('force') && !$this->confirm('Cette action va remplacer les données de la nouvelle base par celles de l\'ancienne. Continuer ?')) {
            $this->warn('Import annulé.');
            return self::SUCCESS;
        }

        $old = $this->makeOldPdo($oldUrl);
        $new = DB::connection()->getPdo();

        $this->info('Connexion ancienne base : OK');
        $this->info('Connexion nouvelle base : OK');

        $oldTables = $this->tables($old);
        $newTables = $this->tables($new);
        $tables = array_values(array_intersect($oldTables, $newTables));

        if (empty($tables)) {
            $this->error('Aucune table commune trouvée entre ancienne et nouvelle base.');
            return self::FAILURE;
        }

        $this->info('Tables communes trouvées : '.count($tables));

        $new->exec('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            $new->exec('TRUNCATE TABLE '.$this->qi($table));
        }

        $totalRows = 0;

        foreach ($tables as $table) {
            $oldColumns = $this->columns($old, $table);
            $newColumns = $this->columns($new, $table);
            $columns = array_values(array_intersect($oldColumns, $newColumns));

            if (empty($columns)) {
                $this->warn("Table {$table} ignorée : aucune colonne commune.");
                continue;
            }

            $count = (int) $old->query('SELECT COUNT(*) FROM '.$this->qi($table))->fetchColumn();

            if ($count === 0) {
                $this->line("{$table} : 0 ligne");
                continue;
            }

            $inserted = 0;
            $limit = 300;

            for ($offset = 0; $offset < $count; $offset += $limit) {
                $selectSql = 'SELECT '.implode(',', array_map([$this, 'qi'], $columns)).' FROM '.$this->qi($table).' LIMIT '.$limit.' OFFSET '.$offset;
                $rows = $old->query($selectSql)->fetchAll(PDO::FETCH_ASSOC);

                if (!$rows) {
                    continue;
                }

                $placeholders = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';
                $insertSql = 'INSERT INTO '.$this->qi($table).' ('.implode(',', array_map([$this, 'qi'], $columns)).') VALUES '.$placeholders;
                $stmt = $new->prepare($insertSql);

                foreach ($rows as $row) {
                    $values = [];
                    foreach ($columns as $column) {
                        $values[] = $row[$column] ?? null;
                    }
                    $stmt->execute($values);
                    $inserted++;
                    $totalRows++;
                }
            }

            $this->line("{$table} : {$inserted} ligne(s) importée(s)");
        }

        $new->exec('SET FOREIGN_KEY_CHECKS=1');

        $this->info('Import terminé. Total lignes importées : '.$totalRows);

        return self::SUCCESS;
    }

    protected function makeOldPdo(string $url): PDO
    {
        $parts = parse_url($url);

        if (!$parts || empty($parts['host'])) {
            throw new \RuntimeException('OLD_DATABASE_URL invalide.');
        }

        $host = $parts['host'];
        $port = $parts['port'] ?? 3306;
        $user = urldecode($parts['user'] ?? 'root');
        $pass = urldecode($parts['pass'] ?? '');
        $database = ltrim($parts['path'] ?? '', '/');

        if ($database === '') {
            $database = 'railway';
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 20,
        ]);
    }

    protected function tables(PDO $pdo): array
    {
        $rows = $pdo->query('SHOW FULL TABLES')->fetchAll(PDO::FETCH_NUM);
        $tables = [];

        foreach ($rows as $row) {
            if (($row[1] ?? 'BASE TABLE') === 'BASE TABLE') {
                $tables[] = $row[0];
            }
        }

        sort($tables);
        return $tables;
    }

    protected function columns(PDO $pdo, string $table): array
    {
        $rows = $pdo->query('SHOW COLUMNS FROM '.$this->qi($table))->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn ($row) => $row['Field'], $rows);
    }

    protected function qi(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }
}
