<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

$key = $_GET['key'] ?? '';

if ($key !== 'timah2026') {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'forbidden',
        'message' => 'Accès refusé.',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

set_time_limit(0);
ini_set('memory_limit', '512M');

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

header('Content-Type: application/json; charset=utf-8');

try {
    if (!env('OLD_DATABASE_URL')) {
        http_response_code(422);
        echo json_encode([
            'status' => 'missing_old_database_url',
            'message' => 'Ajoutez OLD_DATABASE_URL dans les variables Railway du service Timahschool, puis redéployez.',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    Artisan::call('timah:import-old-db', ['--force' => true]);

    echo json_encode([
        'status' => 'done',
        'message' => 'Import ancienne base terminé.',
        'output' => Artisan::output(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'output' => Artisan::output(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
