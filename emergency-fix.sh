#!/bin/bash

# Laravel Emergency Fix Script
# Herstel composer autoloading en clear alle caches

echo "🚨 Laravel Emergency Fix - Herstel Autoloading"
echo "=============================================="

# Stap 1: Dump autoload opnieuw
echo "📦 Herstel Composer Autoloading..."
composer dump-autoload --optimize

# Stap 2: Clear alle Laravel caches
echo "🧹 Clear Laravel Caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Stap 3: Herstel config cache (voorzichtig)
echo "⚙️ Rebuild Config Cache..."
php artisan config:cache

# Stap 4: Rebuild autoload
echo "🔄 Rebuild Autoload Files..."
composer install --no-dev --optimize-autoloader

# Stap 5: Test applicatie
echo "🧪 Test Applicatie..."
php artisan --version

echo "✅ Emergency fix voltooid!"
echo "🌐 Probeer nu http://127.0.0.1:8000"