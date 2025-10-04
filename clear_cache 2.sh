#!/bin/bash

echo "=== CACHE CLEARING ==="

# Clear Laravel cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

echo "✅ Alle cache gecleared"
echo "Refresh nu je browser met Cmd+Shift+R (Mac) of Ctrl+Shift+R (Windows)"