<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// 1. Prepare writable storage and cache paths in /tmp (Vercel serverless environment)
$storageDirs = [
    '/tmp/storage',
    '/tmp/storage/app',
    '/tmp/storage/framework',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
    '/tmp/views',
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// 2. Copy the SQLite database to /tmp so it is writable and available
$sourceDb = dirname(__DIR__) . '/database/database.sqlite';
$targetDb = '/tmp/database.sqlite';

if (!file_exists($targetDb) || filesize($targetDb) === 0) {
    if (file_exists($sourceDb) && filesize($sourceDb) > 0) {
        copy($sourceDb, $targetDb);
    } else {
        touch($targetDb);
    }
}

// 3. Set environment variables for serverless runtime
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = $targetDb;
putenv('DB_CONNECTION=sqlite');
putenv("DB_DATABASE={$targetDb}");
$_SERVER['DB_CONNECTION'] = 'sqlite';
$_SERVER['DB_DATABASE'] = $targetDb;
$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PORT'] = 443;

$_ENV['VIEW_COMPILED_PATH'] = '/tmp/views';
putenv('VIEW_COMPILED_PATH=/tmp/views');
$_SERVER['VIEW_COMPILED_PATH'] = '/tmp/views';

define('LARAVEL_START', microtime(true));

// 4. Register Composer Autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// 5. Bootstrap Laravel
/** @var Application $app */
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';

// Tell Laravel to use /tmp/storage for writable files and explicitly bind SQLite path
$app->useStoragePath('/tmp/storage');
$app->booted(function () use ($targetDb) {
    \Illuminate\Support\Facades\URL::forceScheme('https');
    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => $targetDb,
        'view.compiled' => '/tmp/views',
    ]);
});

// 6. Handle incoming request
$app->handleRequest(Request::capture());
