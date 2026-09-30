<?php

// Front door for a host where the web root (htdocs) is the only folder PHP may use: the Laravel app itself
// lives in ./_app (closed to visitors by .htaccess) and only this file, the assets and the uploads are public.

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$app = __DIR__.'/_app';

if (file_exists($maintenance = $app.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $app.'/vendor/autoload.php';

/** @var Illuminate\Foundation\Application $laravel */
$laravel = require_once $app.'/bootstrap/app.php';

// Assets (build/, images/) sit next to this file, not in _app/public.
$laravel->usePublicPath(__DIR__);

$laravel->handleRequest(Request::capture());
