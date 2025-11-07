<?php
/**
 * CACHE CLEANER - Upload naar httpd.www/clear-cache.php
 * Verwijder na gebruik!
 */

// Security
define('CLEAR_PASSWORD', 'bonami2025clear');

if (!isset($_GET['pass']) || $_GET['pass'] !== CLEAR_PASSWORD) {
    die('Access denied');
}

echo "<h1>🧹 Cache Cleaner</h1>";
echo "<pre>";

// Ga naar root (One.com specifieke structuur)
// httpd.www = public folder  
// httpd.private = Laravel root
$rootDir = __DIR__ . '/../httpd.private';

if (!is_dir($rootDir)) {
    // Fallback voor lokale ontwikkeling
    $rootDir = __DIR__ . '/..';
}

chdir($rootDir);
echo "Working directory: " . getcwd() . "\n\n";

// Clear bootstrap cache
echo "=== BOOTSTRAP CACHE ===\n";
$bootstrapCache = 'bootstrap/cache';
if (is_dir($bootstrapCache)) {
    $files = glob($bootstrapCache . '/*.php');
    foreach ($files as $file) {
        if (basename($file) !== '.gitignore') {
            unlink($file);
            echo "✓ Deleted: " . basename($file) . "\n";
        }
    }
    echo "✓ Bootstrap cache cleared\n";
} else {
    echo "✗ Bootstrap cache directory not found\n";
}

echo "\n=== STORAGE CACHE ===\n";
// Clear storage cache
$storageDirs = [
    'storage/framework/cache/data',
    'storage/framework/views',
    'storage/framework/sessions'
];

foreach ($storageDirs as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '/*');
        $count = 0;
        foreach ($files as $file) {
            if (is_file($file) && basename($file) !== '.gitignore') {
                unlink($file);
                $count++;
            }
        }
        echo "✓ Cleared $count files from $dir\n";
    }
}

echo "\n=== PERMISSIONS CHECK ===\n";
$checkDirs = [
    'storage',
    'storage/logs',
    'storage/framework',
    'bootstrap/cache'
];

foreach ($checkDirs as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $writable = is_writable($dir) ? '✓ Writable' : '✗ NOT writable';
        echo "$dir: $perms - $writable\n";
        
        // Try to fix permissions
        if (!is_writable($dir)) {
            @chmod($dir, 0775);
            echo "  → Attempted to set 775\n";
        }
    }
}

echo "\n=== DONE ===\n";
echo "✅ Cache cleared!\n";
echo "Try refreshing your site now.\n";
echo "\n⚠️ Delete this file after use!\n";

echo "</pre>";
