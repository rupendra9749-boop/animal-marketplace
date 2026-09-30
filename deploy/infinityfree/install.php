<?php

// Runs over HTTP (the free host has no SSH): applies new database migrations, refreshes the package list and
// clears the caches. It can NEVER wipe data - there is no "fresh" option any more. The deploy pipeline uploads this
// file with a one-time token for each deploy and deletes it again afterwards.
// Usage: install.php?t=TOKEN
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

$steps = [
    ['package:discover', []],
    ['migrate', ['--force' => true]],
    ['db:seed', ['--class' => 'ProductionSeeder', '--force' => true]],
    ['optimize:clear', []],
];

foreach ($steps as [$command, $arguments]) {
    echo "== php artisan $command ==\n";
    try {
        $code = Artisan::call($command, $arguments);
        echo trim(Artisan::output())."\n(exit code $code)\n\n";
        if ($code !== 0) {
            echo "FAILED: $command exited with code $code\n";
            exit(1);
        }
    } catch (Throwable $e) {
        echo 'FAILED: '.get_class($e).': '.$e->getMessage()."\n";
        exit(1);
    }
}

echo "Setup finished.\n";
