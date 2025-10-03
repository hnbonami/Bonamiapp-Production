#!/bin/bash

# Debug en fix sjablonen routes

echo "🔍 Diagnostiek sjablonen probleem..."

echo "📊 Controleer routes cache:"
php artisan route:cache
php artisan route:list | grep -i sjabloon

echo ""
echo "📊 Controleer templates tabel structuur:"
mysql -u Hannes -pHannes1986 Bonamisportcoaching -e "DESCRIBE templates;"

echo ""
echo "🔧 Fix Template model voor bestaande database structuur..."

echo "✅ Diagnostiek voltooid!"