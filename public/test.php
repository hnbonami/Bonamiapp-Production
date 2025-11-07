<?php
// Basis test file voor server debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Server Test</h1>";
echo "<p>PHP versie: " . phpversion() . "</p>";
echo "<p>Current directory: " . __DIR__ . "</p>";
echo "<p>Parent directory: " . dirname(__DIR__) . "</p>";

echo "<h2>Directory Scan:</h2>";
echo "<h3>Current dir bestanden:</h3>";
echo "<pre>" . print_r(scandir(__DIR__), true) . "</pre>";

echo "<h3>Parent dir bestanden:</h3>";
$parentDir = dirname(__DIR__);
if (is_readable($parentDir)) {
    echo "<pre>" . print_r(scandir($parentDir), true) . "</pre>";
} else {
    echo "Kan parent dir niet lezen<br>";
}

echo "<h3>Grandparent dir bestanden:</h3>";
$grandParentDir = dirname(dirname(__DIR__));
if (is_readable($grandParentDir)) {
    echo "<pre>" . print_r(scandir($grandParentDir), true) . "</pre>";
} else {
    echo "Kan grandparent dir niet lezen<br>";
}

echo "<h2>File Checks:</h2>";
$pathsToCheck = [
    __DIR__.'/../vendor/autoload.php',
    __DIR__.'/../bootstrap/app.php',
    __DIR__.'/../../httpd.private/vendor/autoload.php',
    __DIR__.'/../../httpd.private/bootstrap/app.php',
    __DIR__.'/../httpd.private/vendor/autoload.php',
    __DIR__.'/../httpd.private/bootstrap/app.php',
];

foreach ($pathsToCheck as $path) {
    echo $path . " => " . (file_exists($path) ? '<strong style="color:green">BESTAAT</strong>' : '<span style="color:red">BESTAAT NIET</span>') . "<br>";
}

echo "<h2>Test Geslaagd!</h2>";
echo "<p>Als je dit ziet, werkt PHP correct op de server.</p>";