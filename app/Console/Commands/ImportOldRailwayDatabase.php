<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;
use PDOException;
use Throwable;

class ImportOldRailwayDatabase extends Command
{
    protected $signature = 'timah:import-old-db {--force : Confirmer l\'import sans interaction}';

    protected $description = 'Importe les données de l\'ancienne base Railway vers la base active.';

    protected string $oldUrl = '';

    public function handle(): int
    {
        $this->oldUrl = (string) env('OLD_DATABASE_URL');

        if (!$this->oldUrl) {
            $this->error('OLD_DATABASE_URL est absent. Ajoutez la variable dans Railway avant de relancer.');
            return self::FAILURE;
        }

        if (!$this->option('force') && !$this->confirm('Cette action va remplacer les données de la nouvelle base par celles de l\'ancienne. Continuer ?')) {
            $this->warn('Import annulé.');
            return self::SUCCESS;
        }

        $old = $this->makeOldPdo();
        $new = $this->makeNewPdo();

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
            $this->line('Nettoyage : '.$table);
            $new = $this->retryPdo(function () use ($new, $table) {
                $new->exec('TRUNCATE TABLE '.$this->qi($table));
                return $new;
            }, $new, true);
        }

        $totalRows = 0;

        foreach ($tables as $table) {
            $this->line('Import table : '.$table);

            $old = $this->makeOldPdo();
            $new = $this->makeNewPdo();
            $new->exec('SET FOREIGN_KEY_CHECKS=0');

            $oldColumns = $this->columns($old, $table);
            $newColumns = $this->columns($new, $table);
            $columns = array_values(array_intersect($oldColumns, $newColumns));

            if (empty($columns)) {
                $this->warn("Table {$table} ignorée : aucune colonne commune.");
                continue;
            }

            $count = (int) $this->retryPdo(function () use ($old, $table) {
                return $old->query('SELECT COUNT(*) FROM '.$this->qi($table))->fetchColumn();
            }, $old, false);

            if ($count === 0) {
                $this->line("{$table} : 0 ligne");
                continue;
            }

            $inserted = 0;
            $limit = 50;

            for ($offset = 0; $offset < $count; $offset += $limit) {
                $attempt = 1;

                while (true) {
                    try {
                        $old = $this->pingOrReconnect($old, false);
                        $new = $this->pingOrReconnect($new, true);
                        $new->exec('SET FOREIGN_KEY_CHECKS=0');

                        $selectSql = 'SELECT '.implode(',', array_map([$this, 'qi'], $columns)).' FROM '.$this->qi($table).' LIMIT '.$limit.' OFFSET '.$offset;
                        $rows = $old->query($selectSql)->fetchAll(PDO::FETCH_ASSOC);

                        if (!$rows) {
                            break;
                        }

                        $placeholders = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';
                        $insertSql = 'REPLACE INTO '.$this->qi($table).' ('.implode(',', array_map([$this, 'qi'], $columns)).') VALUES '.$placeholders;
                        $stmt = $new->prepare($insertSql);

                        foreach ($rows as $row) {
                            $values = [];
                            foreach ($columns as $column) {
                                $values[] = $row[$column] ?? null;
                            }
                            $stmt->execute($values);
                        }

                        $inserted += count($rows);
                        $totalRows += count($rows);
                        break;
                    } catch (Throwable $e) {
                        if (!$this->isConnectionLost($e) || $attempt >= 5) {
                            throw $e;
                        }

                        $this->warn("Connexion MySQL perdue sur {$table} offset {$offset}. Reconnexion tentative {$attempt}/5...");
                        sleep(2);
                        $old = $this->makeOldPdo();
                        $new = $this->makeNewPdo();
                        $attempt++;
                    }
                }
            }

            $this->line("{$table} : {$inserted} ligne(s) importée(s)");
        }

        $new = $this->makeNewPdo();
        $new->exec('SET FOREIGN_KEY_CHECKS=1');

        $this->info('Import terminé. Total lignes importées : '.$totalRows);

        return self::SUCCESS;
    }

    protected function makeOldPdo(): PDO
    {
        $parts = parse_url($this->oldUrl);

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

        return $this->newPdo($host, (string) $port, $database, $user, $pass);
    }

    protected function makeNewPdo(): PDO
    {
        $connection = config('database.connections.mysql');

        return $this->newPdo(
            (string) $connection['host'],
            (string) $connection['port'],
            (string) $connection['database'],
            (string) $connection['username'],
            (string) $connection['password']
        );
    }

    protected function newPdo(string $host, string $port, string $database, string $user, string $pass): PDO
    {
        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 30,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        ]);

        try {
            $pdo->exec('SET SESSION wait_timeout=28800');
            $pdo->exec('SET SESSION interactive_timeout=28800');
        } catch (Throwable $e) {
            // Certains hébergeurs refusent ces réglages. On continue.
        }

        return $pdo;
    }

    protected function pingOrReconnect(PDO $pdo, bool $new): PDO
    {
        try {
            $pdo->query('SELECT 1')->fetchColumn();
            return $pdo;
        } catch (Throwable $e) {
            return $new ? $this->makeNewPdo() : $this->makeOldPdo();
        }
    }

    protected function retryPdo(callable $callback, PDO $pdo, bool $new)
    {
        $attempt = 1;

        while (true) {
            try {
                $pdo = $this->pingOrReconnect($pdo, $new);
                return $callback();
            } catch (Throwable $e) {
                if (!$this->isConnectionLost($e) || $attempt >= 5) {
                    throw $e;
                }

                sleep(2);
                $pdo = $new ? $this->makeNewPdo() : $this->makeOldPdo();
                $attempt++;
            }
        }
    }

    protected function isConnectionLost(Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'server has gone away')
            || str_contains($message, 'lost connection')
            || str_contains($message, 'connection refused')
            || str_contains($message, 'connection timed out')
            || str_contains($message, 'broken pipe')
            || str_contains($message, 'mysql not ready');
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
