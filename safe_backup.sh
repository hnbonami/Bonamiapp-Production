# Check git status
git status

# Als er nog uncommitted changes zijn, commit en push dan:
git add .
git commit -m "🔧 FIX: Klanten address fields and list improvements"
git push origin main

# Database backup via tinker (veilige versie)
php artisan tinker << 'EOF'
$backup = [];
$tables = ['users', 'klanten', 'bikefits', 'templates', 'sjablonen', 'sjabloon_paginas', 'inspanningstesten'];
foreach($tables as $table) {
    if (Schema::hasTable($table)) {
        $backup[$table] = DB::table($table)->get()->toArray();
        echo "Backed up table: $table\n";
    }
}
file_put_contents('database_backup_'.date('Y-m-d_H-i-s').'.json', json_encode($backup, JSON_PRETTY_PRINT));
echo "Database backup completed!\n";
exit
EOF

# Maak ook een migratie backup van alle tabellen
php artisan schema:dump --database=mysql --path=database/schema/mysql-schema.sql

echo "✅ Git push completed"
echo "✅ Database data backup completed" 
echo "✅ Database schema dump completed"