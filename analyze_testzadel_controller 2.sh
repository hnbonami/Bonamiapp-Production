#!/bin/bash

# Analyseer TestzadelController om te zien hoe het werkt
echo "=== TESTZADEL CONTROLLER ANALYSE ==="

# Bekijk TestzadelController
if [ -f "app/Http/Controllers/TestzadelController.php" ]; then
    echo "TestzadelController gevonden!"
    echo "Eerste 50 regels:"
    head -50 app/Http/Controllers/TestzadelController.php
else
    echo "TestzadelController niet gevonden"
fi

echo -e "\n=== BIKEFIT RELATIE CHECK ==="
# Check of testzadel velden in bikefit tabel staan
php artisan tinker --execute="
try {
    \$columns = Schema::getColumnListing('bikefits');
    \$testzadelColumns = array_filter(\$columns, function(\$col) {
        return strpos(strtolower(\$col), 'zadel') !== false || strpos(strtolower(\$col), 'test') !== false;
    });
    echo 'Testzadel-gerelateerde kolommen in bikefits:' . PHP_EOL;
    print_r(\$testzadelColumns);
    
    echo PHP_EOL . 'Voorbeeld bikefit met testzadel info:' . PHP_EOL;
    \$sample = DB::table('bikefits')->whereNotNull('nieuw_testzadel')->first();
    if (\$sample) {
        print_r(\$sample);
    } else {
        echo 'Geen bikefits met testzadel gevonden' . PHP_EOL;
    }
} catch (Exception \$e) {
    echo 'Error: ' . \$e->getMessage() . PHP_EOL;
}
"