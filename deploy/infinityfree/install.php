<?php

// One-time setup, run over HTTP: creates the database tables and the starting data (animal types + one admin).
// Delete this file afterwards. Usage: install.php?t=TOKEN
if (! hash_equals('@@TOKEN@@', (string) ($_GET['t'] ?? ''))) {
    http_response_code(404);
    exit;
}

header('Content-Type: text/plain');
if (function_exists('set_time_limit')) {
    @set_time_limit(120);
}

$app = __DIR__.'/_app';
require $app.'/vendor/autoload.php';

/** @var Illuminate\Foundation\Application $laravel */
$laravel = require $app.'/bootstrap/app.php';
$laravel->usePublicPath(__DIR__);
$laravel->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Artisan;

// ?fresh=1 wipes the tables first: only for the very first setup, or to start over from an empty database.
$steps = [
    isset($_GET['fresh']) ? ['migrate:fresh', ['--force' => true]] : ['migrate', ['--force' => true]],
    ['db:seed', ['--class' => 'ProductionSeeder', '--force' => true]],
    ['optimize:clear', []],
];

foreach ($steps as [$command, $arguments]) {
    echo "== php artisan $command ==\n";
    try {
        $code = Artisan::call($command, $arguments);
        echo trim(Artisan::output())."\n(exit code $code)\n\n";
    } catch (Throwable $e) {
        echo 'FAILED: '.get_class($e).': '.$e->getMessage()."\n";
        exit(1);
    }
}

echo "Setup finished.\n";
