# Analyseer huidige testzadels systeem
echo "=== TESTZADELS SYSTEEM ANALYSE ==="

# Check of testzadels tabel bestaat
php artisan tinker --execute="
try {
    echo 'Testzadels tabel kolommen:\n';
    print_r(Schema::getColumnListing('testzadels'));
    echo '\n\nVoorbeeld testzadel:\n';
    \$sample = DB::table('testzadels')->first();
    if (\$sample) {
        print_r(\$sample);
    } else {
        echo 'Geen testzadels gevonden\n';
    }
} catch (Exception \$e) {
    echo 'Testzadels tabel bestaat niet: ' . \$e->getMessage() . '\n';
}
"

# Zoek testzadels controller/routes
find . -name "*testzadel*" -type f | grep -E "\.(php|blade\.php)$"

# Check routes
grep -r "testzadel" routes/ 2>/dev/null || echo "Geen testzadel routes gevonden"