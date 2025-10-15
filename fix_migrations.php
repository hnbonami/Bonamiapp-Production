<?php
/**
 * Veilige Bulk Migration Fixer
 * Voegt hasTable checks toe aan alle Schema::create() calls
 */

$migrationsDir = __DIR__ . '/database/migrations';
$files = glob($migrationsDir . '/*.php');
$patched = 0;
$skipped = 0;

echo "🔧 Starten met bulk fix van migrations...\n\n";

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Skip als al gepatcht
    if (strpos($content, 'Schema::hasTable') !== false) {
        echo "⏭️  Skip (al gepatcht): " . basename($file) . "\n";
        $skipped++;
        continue;
    }
    
    // Check of file Schema::create bevat
    if (strpos($content, 'Schema::create') === false) {
        continue;
    }
    
    // Extract table naam uit Schema::create('table_name'
    if (preg_match("/Schema::create\('([^']+)'/", $content, $matches)) {
        $tableName = $matches[1];
        
        // Voeg veilige check toe NA "public function up()..." lijn
        $pattern = "/(public function up\(\)[^\{]*\{)/";
        $replacement = "$1\n        // Skip als de tabel al bestaat - VEILIGE CHECK\n        if (Schema::hasTable('$tableName')) {\n            return;\n        }\n        ";
        
        $newContent = preg_replace($pattern, $replacement, $content, 1);
        
        if ($newContent && $newContent !== $content) {
            file_put_contents($file, $newContent);
            echo "✅ Gepatcht: " . basename($file) . " (tabel: $tableName)\n";
            $patched++;
        }
    }
}

echo "\n🎉 Klaar!\n";
echo "✅ Gepatcht: $patched files\n";
echo "⏭️  Overgeslagen: $skipped files (al gepatcht)\n";
echo "\n▶️  Run nu: php artisan migrate\n";
