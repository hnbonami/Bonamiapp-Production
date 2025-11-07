<?php
/**
 * VEILIGE BROWSER MIGRATIE TOOL - One.com Compatible
 * Upload naar: /httpd.www/browser-migrate.php
 * Verwijder direct na gebruik!
 */

// Security
define('MIGRATE_PASSWORD', 'bonami2025migrate');

if (!isset($_GET['pass']) || $_GET['pass'] !== MIGRATE_PASSWORD) {
    http_response_code(403);
    die('Access denied');
}

// Ga naar root (One.com specifieke structuur)
$rootDir = '/customers/5/a/2/hannesbonami.be/httpd.private';
chdir($rootDir);

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bonami - Database Migratie</title>
    <style>
        body { font-family: system-ui; max-width: 1200px; margin: 40px auto; padding: 0 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #2563eb; border-bottom: 2px solid #2563eb; padding-bottom: 10px; }
        h2 { color: #374151; margin-top: 30px; }
        pre { background: #1f2937; color: #10b981; padding: 20px; border-radius: 6px; overflow-x: auto; 
              font-size: 13px; line-height: 1.5; }
        .button { display: inline-block; background: #2563eb; color: white; padding: 12px 24px; 
                  border-radius: 6px; text-decoration: none; margin: 5px; border: none; cursor: pointer;
                  font-size: 14px; }
        .button.danger { background: #dc2626; }
        .button.success { background: #059669; }
        .button.warning { background: #f59e0b; }
        .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; }
        .success { background: #d1fae5; border-left: 4px solid #059669; padding: 15px; margin: 20px 0; }
        .error { background: #fee2e2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; }
        .info { background: #dbeafe; border-left: 4px solid #2563eb; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Bonami Sportcoaching - Database Migratie</h1>
        
        <?php
        $action = $_GET['action'] ?? 'status';
        
        // Test database connectie
        $dbHost = 'hannesbonami.be.mysql';
        $dbName = 'hannesbonami_bebonamisportcoaching';
        $dbUser = 'hannesbonami_bebonamisportcoaching';
        $dbPass = 'Hannes1986';
        
        try {
            $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo "<div class='success'>";
            echo "✅ <strong>Database verbonden:</strong> $dbName";
            echo "</div>";
            
        } catch (PDOException $e) {
            echo "<div class='error'>";
            echo "❌ <strong>Database connectie fout:</strong> " . htmlspecialchars($e->getMessage());
            echo "<br><br>Check je database credentials!";
            echo "</div>";
            die();
        }
        
        // Voer actie uit
        if ($action === 'migrate-status') {
            echo "<h2>📊 Migration Status</h2>";
            
            try {
                // Haal alle migrations op
                $stmt = $pdo->query("SELECT migration, batch FROM migrations ORDER BY id");
                $ranMigrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<div class='info'>";
                echo "<strong>Uitgevoerde migrations:</strong> " . count($ranMigrations);
                echo "</div>";
                
                // Toon laatste 10 migrations
                echo "<h3>Laatst uitgevoerde migrations:</h3>";
                echo "<pre>";
                $last10 = array_slice(array_reverse($ranMigrations), 0, 10);
                foreach ($last10 as $migration) {
                    echo "✓ " . $migration['migration'] . " (batch " . $migration['batch'] . ")\n";
                }
                echo "</pre>";
                
            } catch (PDOException $e) {
                echo "<div class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
            
        } elseif ($action === 'migrate-run') {
            echo "<h2>🚀 Migrations Uitvoeren</h2>";
            echo "<div class='warning'>⚠️ Database wordt nu bijgewerkt...</div>";
            echo "<pre>";
            
            // Voer migrations uit via artisan
            $command = "cd " . escapeshellarg($rootDir) . " && php artisan migrate --force 2>&1";
            $output = shell_exec($command);
            
            echo htmlspecialchars($output);
            echo "</pre>";
            
            if (strpos($output, 'ERROR') === false && strpos($output, 'Exception') === false) {
                echo "<div class='success'>✅ Migrations voltooid!</div>";
            } else {
                echo "<div class='error'>⚠️ Er zijn mogelijk errors opgetreden. Check de output hierboven.</div>";
            }
            
        } elseif ($action === 'migrate-pretend') {
            echo "<h2>🔍 Preview: SQL Queries</h2>";
            echo "<div class='info'>Dit voert GEEN wijzigingen uit, alleen preview!</div>";
            echo "<pre>";
            
            $command = "cd " . escapeshellarg($rootDir) . " && php artisan migrate --pretend 2>&1";
            $output = shell_exec($command);
            
            echo htmlspecialchars($output);
            echo "</pre>";
            
        } elseif ($action === 'rollback') {
            echo "<h2>⏪ Laatste Migration Rollback</h2>";
            echo "<div class='warning'>⚠️ Laatste migration wordt teruggedraaid...</div>";
            echo "<pre>";
            
            $command = "cd " . escapeshellarg($rootDir) . " && php artisan migrate:rollback --step=1 2>&1";
            $output = shell_exec($command);
            
            echo htmlspecialchars($output);
            echo "</pre>";
            
        } else {
            // Default: Toon status
            echo "<h2>📊 Systeem Informatie</h2>";
            echo "<pre>";
            echo "PHP Version: " . phpversion() . "\n";
            echo "Working Directory: " . getcwd() . "\n";
            echo "Artisan File: " . (file_exists('artisan') ? '✓ Found' : '✗ NOT FOUND') . "\n";
            echo "Vendor Folder: " . (is_dir('vendor') ? '✓ Exists' : '✗ NOT FOUND') . "\n";
            echo ".env File: " . (file_exists('.env') ? '✓ Exists' : '✗ NOT FOUND') . "\n";
            echo "</pre>";
            
            // Check migrations table
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM migrations");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<div class='success'>";
                echo "✅ Migrations tabel gevonden. Totaal uitgevoerde migrations: " . $result['count'];
                echo "</div>";
            } catch (PDOException $e) {
                echo "<div class='error'>";
                echo "❌ Migrations tabel niet gevonden of fout: " . htmlspecialchars($e->getMessage());
                echo "</div>";
            }
        }
        ?>
        
        <h2>⚡ Acties</h2>
        
        <a href="?pass=<?= MIGRATE_PASSWORD ?>" class="button">
            🏠 Home
        </a>
        
        <a href="?pass=<?= MIGRATE_PASSWORD ?>&action=migrate-status" class="button">
            📊 Migration Status
        </a>
        
        <a href="?pass=<?= MIGRATE_PASSWORD ?>&action=migrate-pretend" class="button warning">
            🔍 Preview SQL (veilig)
        </a>
        
        <a href="?pass=<?= MIGRATE_PASSWORD ?>&action=migrate-run" class="button success" 
           onclick="return confirm('⚠️ BELANGRIJK!\n\nWeet je ZEKER dat je de migrations wilt uitvoeren?\n\nDit gaat de database wijzigen!\n\n✓ Heb je een backup gemaakt?\n✓ Heb je de preview bekeken?')">
            🚀 Voer Migrations Uit
        </a>
        
        <a href="?pass=<?= MIGRATE_PASSWORD ?>&action=rollback" class="button danger" 
           onclick="return confirm('Laatste migration terugdraaien?')">
            ⏪ Rollback (laatste)
        </a>
        
        <div class="warning" style="margin-top: 30px;">
            <strong>⚠️ BELANGRIJK NA GEBRUIK:</strong><br>
            1. Verwijder dit bestand: <code>/httpd.www/browser-migrate.php</code><br>
            2. Verwijder ook: <code>/httpd.www/clear-cache.php</code><br>
            3. Verwijder ook: <code>/httpd.www/deploy-helper.php</code> (indien aanwezig)
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #f9fafb; border-radius: 6px;">
            <strong>🔗 Handige Links:</strong><br>
            <a href="../">Terug naar site</a> |
            <a href="clear-cache.php?pass=bonami2025clear" target="_blank">Clear Cache</a>
        </div>
    </div>
</body>
</html>
