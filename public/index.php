<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
// Veilige omgevingsdetectie voor vendor autoload
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    // Lokaal (standaard Laravel structuur)
    require __DIR__.'/../vendor/autoload.php';
} elseif (file_exists(__DIR__.'/../../httpd.private/vendor/autoload.php')) {
    // Server (vendor in httpd.private map)
    require __DIR__.'/../../httpd.private/vendor/autoload.php';
} else {
    die('ERROR: vendor/autoload.php niet gevonden! Controleer of vendor map bestaat.');
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
