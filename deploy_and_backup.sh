#!/bin/bash

# Laravel project directory
cd /Users/hannesbonami/Herd/app/Bonamiapp

# Add alle wijzigingen
git add .

# Commit met een duidelijke beschrijving
git commit -m "🔧 FIX: Complete klanten address fields and improve list sorting

✅ Fixed klanten address fields (straatnaam, huisnummer, postcode, stad)
- Updated KlantenController validation to match view field names
- Address data now saves correctly in both create and edit forms

✅ Improved klanten list functionality  
- Changed 'Laatste consult' to 'Datum toegevoegd'
- Sorted klanten chronologically (newest first)
- Shows actual creation date instead of empty field

✅ Additional fixes
- Fixed bikefit zadel_lengte_center_top validation in edit
- Added steunzolen toggle functionality to create form
- Made zadeltil field accept positive/negative values
- Fixed SVG legend text visibility in results view"

# Push naar remote repository
git push origin main

# Database backup via Laravel Tinker
php artisan tinker --execute="
\$backup = [];
\$tables = ['users', 'klanten', 'bikefits', 'templates', 'sjablonen', 'sjabloon_paginas', 'inspanningstesten'];
foreach(\$tables as \$table) {
    if (Schema::hasTable(\$table)) {
        \$backup[\$table] = DB::table(\$table)->get()->toArray();
        echo \"Backed up table: \$table\n\";
    }
}
file_put_contents('database_backup_'.date('Y-m-d_H-i-s').'.json', json_encode(\$backup, JSON_PRETTY_PRINT));
echo \"Database backup completed!\n\";
"

echo "✅ Git commit & push completed"
echo "✅ Database backup completed"
echo "🎉 All done!"