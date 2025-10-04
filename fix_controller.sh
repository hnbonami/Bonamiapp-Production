#!/bin/bash
echo "🔄 Clearing cache and fixing routes..."
cd /Users/hannesbonami/Desktop/Bonamiapp

php artisan route:clear
php artisan config:clear
php artisan cache:clear
composer dump-autoload

echo "✅ Cache cleared!"
echo "🚀 Now restart your server: php artisan serve"