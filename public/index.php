<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
// Veilige omgevingsdetectie voor vendor autoload en bootstrap
if (file_exists(__DIR__.'/../vendor/autoload.php') && file_exists(__DIR__.'/../bootstrap/app.php')) {
    // Lokaal (standaard Laravel structuur)
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
} elseif (file_exists(__DIR__.'/../httpd.private/vendor/autoload.php') && file_exists(__DIR__.'/../httpd.private/bootstrap/app.php')) {
    // Server (httpd.private naast httpd.www)
    require __DIR__.'/../httpd.private/vendor/autoload.php';
    $app = require_once __DIR__.'/../httpd.private/bootstrap/app.php';
} else {
    die('ERROR: Benodigde Laravel bestanden niet gevonden! Controleer of de vendor en bootstrap mappen bestaan.');
}

// Bootstrap Laravel and handle the request...
$app->handleRequest(Request::capture());
