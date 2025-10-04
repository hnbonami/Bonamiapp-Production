#!/bin/bash
echo "🔄 Clearing Laravel cache and routes..."
cd /Users/hannesbonami/Desktop/Bonamiapp

# Clear all caches
php artisan route:clear
php artisan config:clear  
php artisan cache:clear
php artisan view:clear

# Regenerate autoloader
composer dump-autoload

# Cache routes again
php artisan route:cache
php artisan config:cache

echo "✅ Cache cleared and regenerated!"
echo "🚀 You can now restart your development server with: php artisan serve"