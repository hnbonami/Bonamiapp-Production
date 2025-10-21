# 🔧 PUBLIC FOLDER FIX - Als je public/ niet als document root kunt instellen

## Huidige /public/index.php (Laravel standaard):

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
```

## Wijzig naar (als public/ in root staat):

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/bootstrap/app.php')
    ->handleRequest(Request::capture());
```

**Verschil:** Alle `/../` worden `/` (één niveau minder diep)

## Dan verplaats ook .htaccess uit public/ naar root!

Kopieer `/public/.htaccess` naar `/public_html/.htaccess`
