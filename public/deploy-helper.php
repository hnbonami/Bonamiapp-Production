<?php
/**
 * TIJDELIJKE DEPLOYMENT HELPER
 * Upload naar: /public/deploy-helper.php
 * Verwijder direct na gebruik!
 */

// Security: Alleen toegankelijk met geheim wachtwoord
define('DEPLOY_PASSWORD', 'bonami2025temp'); // Verander dit!

if (!isset($_GET['pass']) || $_GET['pass'] !== DEPLOY_PASSWORD) {
    die('Access denied');
}

echo "<h1>🔧 Bonami Deployment Helper</h1>";
echo "<pre>";

// Ga naar root directory
chdir(__DIR__ . '/..');

echo "📂 Huidige directory: " . getcwd() . "\n\n";

// Check welke actie
$action = $_GET['action'] ?? 'status';

switch ($action) {
    case 'composer':
        echo "📦 Running composer install...\n";
        echo "================================\n";
        exec('composer install --no-dev --optimize-autoloader 2>&1', $output, $return);
        echo implode("\n", $output);
        echo "\n\nReturn code: $return\n";
        break;

    case 'clear-cache':
        echo "🧹 Clearing caches...\n";
        echo "================================\n";
        
        // Clear compiled
        if (file_exists('bootstrap/cache/config.php')) {
            unlink('bootstrap/cache/config.php');
            echo "✓ Config cache cleared\n";
        }
        
        if (file_exists('bootstrap/cache/routes-v7.php')) {
            unlink('bootstrap/cache/routes-v7.php');
            echo "✓ Routes cache cleared\n";
        }
        
        if (file_exists('bootstrap/cache/packages.php')) {
            unlink('bootstrap/cache/packages.php');
            echo "✓ Packages cache cleared\n";
        }
        
        if (file_exists('bootstrap/cache/services.php')) {
            unlink('bootstrap/cache/services.php');
            echo "✓ Services cache cleared\n";
        }
        
        // Clear view cache
        $viewPath = 'storage/framework/views';
        if (is_dir($viewPath)) {
            $files = glob($viewPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            echo "✓ View cache cleared\n";
        }
        
        echo "\n✅ All caches cleared!\n";
        break;

    case 'fix-permissions':
        echo "🔐 Fixing permissions...\n";
        echo "================================\n";
        
        exec('chmod -R 775 storage 2>&1', $output1);
        echo implode("\n", $output1) . "\n";
        echo "✓ Storage permissions fixed\n";
        
        exec('chmod -R 775 bootstrap/cache 2>&1', $output2);
        echo implode("\n", $output2) . "\n";
        echo "✓ Bootstrap cache permissions fixed\n";
        
        break;

    case 'check-env':
        echo "🔍 Checking .env file...\n";
        echo "================================\n";
        
        if (file_exists('.env')) {
            echo "✓ .env exists\n\n";
            
            // Check belangrijke settings (zonder gevoelige data te tonen)
            $env = file_get_contents('.env');
            $checks = [
                'APP_KEY' => strpos($env, 'APP_KEY=base64:') !== false,
                'DB_DATABASE' => strpos($env, 'DB_DATABASE=') !== false,
                'DB_USERNAME' => strpos($env, 'DB_USERNAME=') !== false,
                'DB_PASSWORD' => strpos($env, 'DB_PASSWORD=') !== false,
            ];
            
            foreach ($checks as $key => $exists) {
                echo ($exists ? '✓' : '✗') . " $key " . ($exists ? 'set' : 'MISSING') . "\n";
            }
        } else {
            echo "✗ .env file NOT FOUND!\n";
            echo "⚠️  Copy .env.example to .env first!\n";
        }
        break;

    case 'migrate':
        echo "🗄️  Running migrations...\n";
        echo "================================\n";
        exec('php artisan migrate --force 2>&1', $output, $return);
        echo implode("\n", $output);
        echo "\n\nReturn code: $return\n";
        break;

    case 'logs':
        echo "📋 Recent Laravel Log (last 50 lines)...\n";
        echo "================================\n";
        
        $logFile = 'storage/logs/laravel.log';
        if (file_exists($logFile)) {
            $lines = file($logFile);
            $lastLines = array_slice($lines, -50);
            echo implode('', $lastLines);
        } else {
            echo "No log file found.\n";
        }
        break;

    default:
        echo "📊 System Status\n";
        echo "================================\n";
        echo "PHP Version: " . phpversion() . "\n";
        echo "Laravel Version: " . (file_exists('artisan') ? 'Detected' : 'NOT FOUND') . "\n";
        echo ".env exists: " . (file_exists('.env') ? 'Yes' : 'NO') . "\n";
        echo "vendor exists: " . (is_dir('vendor') ? 'Yes' : 'NO - Run composer!') . "\n";
        echo "\n";
        echo "Storage writable: " . (is_writable('storage') ? 'Yes' : 'NO - Fix permissions!') . "\n";
        echo "Bootstrap cache writable: " . (is_writable('bootstrap/cache') ? 'Yes' : 'NO - Fix permissions!') . "\n";
        echo "\n\n";
        
        echo "Available Actions:\n";
        echo "================================\n";
        echo "?pass=bonami2025temp&action=composer      - Run composer install\n";
        echo "?pass=bonami2025temp&action=clear-cache   - Clear all caches\n";
        echo "?pass=bonami2025temp&action=fix-permissions - Fix storage permissions\n";
        echo "?pass=bonami2025temp&action=check-env     - Check .env configuration\n";
        echo "?pass=bonami2025temp&action=migrate       - Run database migrations\n";
        echo "?pass=bonami2025temp&action=logs          - Show recent log entries\n";
        break;
}

echo "</pre>";
echo "<hr>";
echo "<p>⚠️ <strong>BELANGRIJK:</strong> Verwijder dit bestand direct na gebruik!</p>";
